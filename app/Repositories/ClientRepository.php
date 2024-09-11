<?php

namespace App\Repositories;

use App\Events\ImageUploadEvent;
use App\Exceptions\RepositoryException;
use App\Mail\ClientLoyaltyCardMail;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Scopes\FilterByTelephoneScope;
use App\Services\Contracts\ILoyaltyCardService;
use App\Services\UploadService;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Mail;

class ClientRepository implements ClientRepositoryInterface
{
    
    public function all($filters = [], $withUser = false)
    {
        $query = Client::query();

        if (isset($filters['telephone'])) {
            $telephones = explode(',', $filters['telephone']);
            $query->filterByTelephone($telephones);
        }

        if (isset($filters['sortsurnom'])) {
            $query->orderBy('surnom', 'asc');
        }

        if (isset($filters['sort-surnom'])) {
            $query->orderBy('surnom', 'desc');
        }

        if (isset($filters['comptes'])) {
            if ($filters['comptes'] === 'oui') {
                $query->whereNotNull('user_id');
            } elseif ($filters['comptes'] === 'non') {
                $query->whereNull('user_id');
            }
        }

        if (isset($filters['active'])) {
            if ($filters['active'] === 'oui') {
                $query->whereHas('user', function ($q) {
                    $q->where('active', true);
                });
            } elseif ($filters['active'] === 'non') {
                $query->whereHas('user', function ($q) {
                    $q->where('active', false);
                });
            }
        }

        if ($withUser) {
            return $query->with('user')->get();
        }

        return $query->get();
    }

    public function find($id, $withUser = false)
    {
        if ($withUser) {
            return Client::with('user')->findOrFail($id);
        }

        return Client::findOrFail($id);
    }

    // public function create(array $data)
    // {
    //     return Client::create($data);
    // }

    public function create($data)
    {
        DB::beginTransaction();
    
        try {
            // Récupérer les données client
            $clientData = $data['client'];
    
            // Récupérer les données utilisateur si disponibles
            $userData = isset($data['user']) ? $data['user'] : null;
    
            // Créer le client d'abord pour obtenir l'ID
            $client = Client::create($clientData);
    
            // Si des données utilisateur existent, créer le compte utilisateur
            if ($userData) {
                // Récupérer l'ID du rôle "Client"
                $roleClient = Role::where('name', 'Client')->first();
    
                // Vérifier que le rôle existe
                if (!$roleClient) {
                    throw new RepositoryException('Le rôle "Client" n\'existe pas.');
                }
    
                $userData['role_id'] = $roleClient->id;
    
                // Créer l'utilisateur avec le rôle "Client"
                $user = User::create($userData);
                $client->user()->associate($user);
                $client->save();
            }
            
                // Générer la carte de fidélité (si nécessaire) et obtenir le chemin du PDF
                $loyaltyCardService = app(ILoyaltyCardService::class);
                $pdfPath = $loyaltyCardService->generateLoyaltyCard($client);

                // Envoi de l'email avec la carte de fidélité en pièce jointe
                Mail::to($client->user->email)->send(new ClientLoyaltyCardMail($client, $pdfPath));
    
            DB::commit();
            return [
                'client' => $client->load('user'), // Charge l'utilisateur associé
            ];
        } catch (RepositoryException $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        $client = Client::findOrFail($id);
        $client->update($data);
        return $client;
    }

    public function delete($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
    }

public function registerClientForExistingClient(array $userData, $clientId)
{
    DB::beginTransaction();

    try {
        // Trouver le client
        $client = Client::findOrFail($clientId);

        // Vérifier si le client a déjà un compte utilisateur
        if ($client->user) {
            throw new RepositoryException('Ce client a déjà un compte utilisateur.');
        }

        // Vérifier si le login existe déjà
        if (User::where('login', $userData['login'])->exists()) {
            throw new RepositoryException('Le login existe déjà.');
        }

        // Associer un rôle "Client" à l'utilisateur
        $roleClient = Role::where('name', 'Client')->firstOrFail();
        $userData['role_id'] = $roleClient->id;

        // Créer l'utilisateur et associer au client
        $user = User::create($userData);
        $client->user()->associate($user);
        $client->save();

        // Générer la carte de fidélité (si nécessaire) et obtenir le chemin du PDF
        $loyaltyCardService = app(ILoyaltyCardService::class);
        $pdfPath = $loyaltyCardService->generateLoyaltyCard($client);

        // Envoi de l'email avec la carte de fidélité en pièce jointe
        Mail::to($client->user->email)->send(new ClientLoyaltyCardMail($client, $pdfPath));

        DB::commit();

        return [
            'statut' => 'Success',
            'data' => [
                'client' => $client,
            ],
            'message' => 'Compte utilisateur créé avec succès pour le client, et email envoyé.',
            'httpStatus' => 201
        ];

    } catch (RepositoryException $e) {
        DB::rollBack();
        throw $e;
    }
}



    // public function registerClientForExistingClient(array $userData, $clientId)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $client = Client::findOrFail($clientId);

    //         if ($client->user) {
    //             throw new RepositoryException('Ce client a déjà un compte utilisateur.');
    //         }

    //         if (User::where('login', $userData['login'])->exists()) {
    //             throw new RepositoryException('Le login existe déjà.');
    //         }

    //         $roleClient = Role::where('name', 'Client')->firstOrFail();
    //         $userData['role_id'] = $roleClient->id;

    //         $user = User::create($userData);
    //         $client->user()->associate($user);
    //         $client->save();

    //         DB::commit();
    //         return [
    //             'statut' => 'Success',
    //             'data' => [
    //                 'client' => $client,
    //             ],
    //             'message' => 'Compte utilisateur créé avec succès pour le client.',
    //             'httpStatus' => 201
    //         ];

    //     } catch (RepositoryException $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }


    public function generateLoyaltyCard(Client $client)
    {
        $qrCode = new QrCode("Client: {$client->telephone}");
        $writer = new PngWriter();
        $qrCodeFileName = 'loyalty_card_' . $client->id . '.png';
        $result = $writer->write($qrCode);
        Storage::disk('public')->put('images/' . $qrCodeFileName, $result->getString());

        $client->update([
            'loyalty_card_image' => 'storage/images/' . $qrCodeFileName
        ]);
    }

    public function findByTelephone($telephone)
    {
        return Client::withoutGlobalScope(FilterByTelephoneScope::class)
                      ->where('telephone', $telephone)
                      ->first();
    }

    // public function generateClientPdf(Client $client)
    // {
    //     // Vérifiez si la vue existe
    //     if (!view()->exists('pdf.client_info')) {
    //         throw new \Exception('La vue [pdf.client_info] est introuvable.');
    //     }

    //     $qrCode = new QrCode("Client: {$client->telephone}");
    //     $writer = new PngWriter();
    //     $qrCodePath = 'loyalty_card_' . $client->id . '.png';
    //     $result = $writer->write($qrCode);
    //     Storage::disk('public')->put('images/' . $qrCodePath, $result->getString());

    //     $client->update([
    //         'loyalty_card_image' => 'storage/images/' . $qrCodePath
    //     ]);

    //     // Chargez la vue Blade pour le contenu du PDF
    //     $pdf = PDF::loadView('pdf.client_info', [
    //         'client' => $client,
    //         'qrCodePath' => $qrCodePath,
    //     ]);

    //     // Définir le chemin du fichier PDF
    //     $pdfFilePath = storage_path('app/public/pdfs/client_' . $client->id . '.pdf');

    //     // Sauvegarder le fichier PDF
    //     $pdf->save($pdfFilePath);

    //     return $pdfFilePath;
    // }


//     public function generateClientPdf(Client $client)
// {
//     // Vérifiez si la vue existe
//     if (!view()->exists('pdf.client_info')) {
//         throw new \Exception('La vue [pdf.client_info] est introuvable.');
//     }

//     // Générer le QR Code
//     $qrCode = new QrCode("Client: {$client->telephone}");
//     $writer = new PngWriter();
//     $qrCodeFileName = 'loyalty_card_' . $client->id . '.png';
//     $qrCodePath = 'images/' . $qrCodeFileName; // Chemin relatif public
//     $result = $writer->write($qrCode);

//     // Sauvegarder l'image du QR Code
//     Storage::disk('public')->put($qrCodePath, $result->getString());

//     // Mettre à jour le chemin de la carte de fidélité
//     $client->update([
//         'loyalty_card_image' => $qrCodePath
//     ]);

//     // Charger la vue Blade pour le contenu du PDF
//     $pdf = PDF::loadView('pdf.client_info', [
//         'client' => $client,
//         'qrCodeFileName' => $qrCodeFileName,
//     ]);
//     var_dump($qrCodeFileName);
   
//     $qrCodePath = public_path('storage/' . $qrCodeFileName);
//     // Définir le chemin du fichier PDF
//     $pdfFilePath = storage_path('app/public/pdfs/client_' . $client->id . '.pdf');

//     // Sauvegarder le fichier PDF
//     $pdf->save($pdfFilePath);

//     return $pdfFilePath;
// }

public function generateClientPdf(Client $client)
{
    // Vérifiez si la vue existe
    if (!view()->exists('pdf.client_info')) {
        throw new \Exception('La vue [pdf.client_info] est introuvable.');
    }

    // Générer le QR Code
    $qrCode = new QrCode("Client: {$client->telephone}");
    $writer = new PngWriter();
    $qrCodeFileName = 'loyalty_card_' . $client->id . '.png';
    $qrCodePath = 'images/' . $qrCodeFileName; // Chemin relatif public
    $result = $writer->write($qrCode);

    // Sauvegarder l'image du QR Code
    Storage::disk('public')->put($qrCodePath, $result->getString());

    // Mettre à jour le chemin de la carte de fidélité
    $client->update([
        'loyalty_card_image' => $qrCodePath
    ]);

    // Charger la vue Blade pour le contenu du PDF
    $pdf = PDF::loadView('pdf.client_info', [
        'client' => $client,
        'qrCodeFileName' => asset('storage/' . $qrCodePath), // Utiliser asset pour le chemin complet
        'clientPhoto' => asset('storage/' . $client->user->photo) // Chemin complet pour la photo
    ]);

    // Définir le chemin du fichier PDF
    $pdfFilePath = storage_path('app/public/pdfs/client_' . $client->id . '.pdf');

    // Sauvegarder le fichier PDF
    $pdf->save($pdfFilePath);

    return $pdfFilePath;
}

}
