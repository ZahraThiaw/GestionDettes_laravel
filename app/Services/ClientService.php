<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use App\Repositories\ClientRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\WriterInterface;
use App\Events\ImageUploadEvent;
use App\Mail\ClientLoyaltyCardMail;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;

class ClientService implements ClientServiceInterface
{
    protected $clientRepository;
    protected $uploadService;

    public function __construct(ClientRepositoryInterface $clientRepository, UploadServiceInterface $uploadService)
    {
        $this->clientRepository = $clientRepository;
        $this->uploadService = $uploadService;
    }

    public function getAllClients($filters = [])
    {
        return $this->clientRepository->all($filters, isset($filters['include']) && $filters['include'] === 'user');
    }

    public function getClientById($id, $withUser = false)
    {
        return $this->clientRepository->find($id, $withUser);
    }

    public function getClientsByTelephone($telephone)
    {
        $telephones = explode(',', $telephone);
        return $this->clientRepository->findByTelephone($telephones);
    }

//     public function createClient($data)
// {
//     DB::beginTransaction();

//     try {
//         // Récupérer les données client
//         $clientData = $data['client'];

//         // Récupérer les données utilisateur si disponibles
//         $userData = isset($data['user']) ? $data['user'] : null;

//         // Upload image pour l'utilisateur si fournie
//         if ($userData && isset($userData['photo'])) {
//             $base64Photo = $this->uploadService->uploadImage($userData['photo']);
//             $userData['photo'] = $base64Photo;
//         }

        

//         // Créer le client
//         $client = $this->clientRepository->create($clientData);

//         // Si des données utilisateur existent, créer le compte utilisateur
//         if ($userData) {
//             // Récupérer l'ID du rôle "Client"
//             $roleClient = \App\Models\Role::where('name', 'Client')->first();

//             // Vérifier que le rôle existe
//             if (!$roleClient) {
//                 throw new \Exception('Le rôle "Client" n\'existe pas.');
//             }

//             $userData['role_id'] = $roleClient->id;

//             // Créer l'utilisateur avec le rôle "Client"
//             $user = User::create($userData);

//             // Associer l'utilisateur au client
//             $client->user()->associate($user);
//             $client->save();
//         }

//         // Générer la carte de fidélité avec QR code (si nécessaire)
//         $this->clientRepository->generateLoyaltyCard($client);

//         DB::commit();
//         return $client;
//     } catch (\Exception $e) {
//         DB::rollBack();
//         throw $e;
//     }
// }

// public function createClient($data)
//     {
//         DB::beginTransaction();

//         try {
//             // Récupérer les données client
//             $clientData = $data['client'];

//             // Récupérer les données utilisateur si disponibles
//             $userData = isset($data['user']) ? $data['user'] : null;

//             // Upload de l'image de manière asynchrone ou synchrone
//             if ($userData && isset($userData['photo'])) {
//                 //if ($this->shouldUploadAsync()) {
//                     // Déclencher l'événement pour un upload asynchrone
//                     event(new ImageUploadEvent($userData['photo'], $clientData['id']));
//                 //} 
//                 // else {
//                 //     // Upload synchrone en base64
//                 //     $base64Photo = $this->uploadService->uploadImage($userData['photo']);
//                 //     $userData['photo'] = $base64Photo;
//                 // }
//             }

//             // Créer le client
//             $client = $this->clientRepository->create($clientData);

//             // Si des données utilisateur existent, créer le compte utilisateur
//             if ($userData) {
//                 // Récupérer l'ID du rôle "Client"
//                 $roleClient = \App\Models\Role::where('name', 'Client')->first();

//                 if (!$roleClient) {
//                     throw new \Exception('Le rôle "Client" n\'existe pas.');
//                 }

//                 $userData['role_id'] = $roleClient->id;

//                 // Créer l'utilisateur avec le rôle "Client"
//                 $user = User::create($userData);

//                 // Associer l'utilisateur au client
//                 $client->user()->associate($user);
//                 $client->save();
//             }

//             // Générer la carte de fidélité avec QR code (si nécessaire)
//             $this->clientRepository->generateLoyaltyCard($client);

//             DB::commit();
//             return $client;
//         } catch (\Exception $e) {
//             DB::rollBack();
//             throw $e;
//         }
//     }

public function createClient($data)
{
    DB::beginTransaction();

    try {
        $clientData = $data['client'];
        $userData = $data['user'] ?? null;

        // Création du client
        $client = $this->clientRepository->create($clientData);

        // Si les données utilisateur sont présentes, associez un utilisateur au client
        if ($userData) {
            $roleClient = Role::where('name', 'Client')->first();
            if (!$roleClient) {
                throw new \Exception('Le rôle "Client" n\'existe pas.');
            }

            $userData['role_id'] = $roleClient->id;
            $user = User::create($userData);
            $client->user()->associate($user);
            $client->save();
        }

        // Générer le QR Code et le PDF
        $qrCode = $this->clientRepository->generateLoyaltyCard($client);
        $pdfFilePath = $this->clientRepository->generateClientPdf($client, $qrCode);

        if (!$pdfFilePath || !file_exists($pdfFilePath)) {
            throw new \Exception('Le fichier PDF n\'a pas pu être généré.');
        }

        // Envoyer un e-mail avec le PDF en pièce jointe
        Mail::to($client->user->login)->send(new ClientLoyaltyCardMail($client, $pdfFilePath));

        DB::commit();
        return $client;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

    /**
     * Détermine si l'upload doit être effectué de manière asynchrone.
     */
    protected function shouldUploadAsync()
    {
        // Ici, vous pouvez définir la logique pour choisir entre l'upload async et synchrone.
        // Par exemple, en utilisant une configuration .env ou selon un paramètre dans $data.

        // Ex : utilisation d'une variable d'environnement
        return config('upload.async', false); // true si l'upload asynchrone est activé.
    }

    public function updateClient($id, $data)
    {
        $client = $this->clientRepository->update($id, $data['client']);

        if (isset($data['user'])) {
            if ($client->user_id) {
                $user = User::find($client->user_id);
                $user->update($data['user']);
            } else {
                $user = User::create(array_merge($data['user'], ['role' => 'Client']));
                $client->user_id = $user->id;
                $client->save();
            }
        }

        return $client;
    }

    public function deleteClient($id)
    {
        $this->clientRepository->delete($id);
    }

    public function registerClientForExistingClient(array $userData, $clientId)
    {
        return $this->clientRepository->registerClientForExistingClient($userData, $clientId);
    }

    public function getClientByTelephone($telephone)
    {
        return $this->clientRepository->findByTelephone($telephone);
    }
}
