<?php

namespace App\Repositories;

use App\Events\ImageUploadEvent;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Scopes\FilterByTelephoneScope;
use App\Services\UploadService;
use App\Services\UploadServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;


class ClientRepository implements ClientRepositoryInterface
{
    protected $uploadService;

    public function __construct(UploadServiceInterface $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    
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

    public function create(array $data)
    {
        return Client::create($data);
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

//     public function registerClientForExistingClient(array $userData, $clientId)
// {
//     DB::beginTransaction();

//     try {
//         $client = Client::findOrFail($clientId);

//         if ($client->user) {
//             throw new \Exception('Ce client a déjà un compte utilisateur.');
//         }

//         // Vérifier si le login existe déjà
//         if (User::where('login', $userData['login'])->exists()) {
//             throw new \Exception('Le login existe déjà.');
//         }

//         $roleClient = Role::where('name', 'Client')->firstOrFail();
//         $userData['role_id'] = $roleClient->id;

//         // Téléchargement et encodage de la photo en base64
//         if (isset($userData['photo'])) {
//             $base64Photo = $this->uploadService->uploadImage($userData['photo']);
//             $userData['photo'] = $base64Photo;
//         }

//         // Créer le compte utilisateur
//         $user = User::create($userData);

//         // Associer l'utilisateur au client
//         $client->user()->associate($user);
//         $client->save();

//         // Générer la carte de fidélité avec un QR code
//         $this->generateLoyaltyCard($client);

//         // Inclure le QR code et l'image de la carte de fidélité dans la réponse
//         return [
//             'statut' => 'Success',
//             'data' => [
//                 'client' => $client,
//                 'photo' => $client->photo,
//                 'qr_code' => asset('storage/images/loyalty_card_' . $client->id . '.png') // Chemin vers l'image QR code
//             ],
//             'message' => 'Compte utilisateur créé avec succès pour le client.',
//             'httpStatus' => 201
//         ];

//     } catch (\Exception $e) {
//         DB::rollBack();
//         throw $e;
//     }
// }


public function registerClientForExistingClient(array $userData, $clientId)
    {
        DB::beginTransaction();

        try {
            $client = Client::findOrFail($clientId);

            if ($client->user) {
                throw new \Exception('Ce client a déjà un compte utilisateur.');
            }

            if (User::where('login', $userData['login'])->exists()) {
                throw new \Exception('Le login existe déjà.');
            }

            $roleClient = Role::where('name', 'Client')->firstOrFail();
            $userData['role_id'] = $roleClient->id;

            if (isset($userData['photo'])) {
                if ($this->shouldUploadAsync()) {
                    event(new ImageUploadEvent($userData['photo']));
                } else {
                    $base64Photo = $this->uploadService->uploadImage($userData['photo']);
                    $userData['photo'] = $base64Photo;
                }
            }

            $user = User::create($userData);
            $client->user()->associate($user);
            $client->save();

            $this->generateLoyaltyCard($client);

            DB::commit();
            return [
                'statut' => 'Success',
                'data' => [
                    'client' => $client,
                    'photo' => $client->photo ?? null,
                    'qr_code' => asset('storage/images/loyalty_card_' . $client->id . '.png')
                ],
                'message' => 'Compte utilisateur créé avec succès pour le client.',
                'httpStatus' => 201
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    protected function shouldUploadAsync()
    {
        return config('upload.async', false);
    }

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


    // public function generateLoyaltyCard(Client $client)
    // {
    //     // Créez le QR code avec les informations du client
    //     $qrCode = new QrCode("Client: {$client->id} - {$client->surnom}");
    //     $writer = new PngWriter();

    //     // Définissez le nom du fichier pour l'image QR Code
    //     $qrCodeFileName = 'loyalty_card_' . $client->id . '.png';

    //     // Obtenez les données du QR code sous forme de chaîne binaire
    //     $result = $writer->write($qrCode);

    //     // Sauvegardez le fichier dans le répertoire public/storage/images avec le disque public de Laravel
    //     Storage::disk('public')->put('images/' . $qrCodeFileName, $result->getString());

    //     // Mettez à jour le client avec le chemin de l'image QR Code
    //     $client->update([
    //         'loyalty_card_image' => 'storage/images/' . $qrCodeFileName
    //     ]);
    // }

    // Ajoutez cette méthode pour générer le PDF
    // public function generateClientPdf(Client $client, $qrCodePath)
    // {
    //     // Créez une instance de Dompdf
    //     $dompdf = new Dompdf();

    //     // Chargez la vue Blade pour le contenu du PDF
    //     $pdfcontent =Pdf::loadView('pdf.client_info', [
    //         'client' => $client,
    //         'qrCodePath' => $qrCodePath,
    //     ])->output();

    //     // Chargez le HTML dans Dompdf
    //     $dompdf->loadHtml($pdfcontent);

    //     // Configurez le format du papier
    //     $dompdf->setPaper('A4', 'portrait');

    //     // Rendre le PDF
    //     $dompdf->render();

    //     // Définir le chemin du fichier PDF
    //     $pdfFilePath = storage_path('app/public/client_' . $client->id . '.pdf');

    //     // Sauvegarder le fichier PDF
    //     file_put_contents($pdfFilePath, $dompdf->output());

    //     return $pdfFilePath;
    // }

    public function generateClientPdf(Client $client, $qrCodePath)
    {
        // Vérifiez si la vue existe
        if (!view()->exists('pdf.client_info')) {
            throw new \Exception('La vue [pdf.client_info] est introuvable.');
        }

        // Chargez la vue Blade pour le contenu du PDF
        $pdf = PDF::loadView('pdf.client_info', [
            'client' => $client,
            'qrCodePath' => $qrCodePath,
        ]);

        // Définir le chemin du fichier PDF
        $pdfFilePath = storage_path('app/public/pdfs/client_' . $client->id . '.pdf');

        // Sauvegarder le fichier PDF
        $pdf->save($pdfFilePath);

        return $pdfFilePath;
    }
}
