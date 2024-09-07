<?php

namespace App\Jobs;

use App\Services\Contracts\IUploadService;
use BaconQrCode\Encoder\QrCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
//  use Symfony\Component\HttpFoundation\File\UploadedFile;
 use Illuminate\Http\UploadedFile;

class StoreImageInCloud implements ShouldQueue
{
  
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $tempPath;

    public function __construct($user, $tempPath)
    {
        $this->user = $user;
        $this->tempPath = $tempPath;
       
    }

    public function handle(IUploadService $uploadService)
    {
        // Convertir le chemin temporaire en instance UploadedFile
        $file = new UploadedFile(Storage::path($this->tempPath), basename($this->tempPath));

        // Télécharger le fichier vers le cloud
        $fileUrl = $uploadService->upload($file);
       

        // Mettre à jour l'utilisateur avec l'URL du fichier
        $this->user->update(['photo' => $fileUrl]);

        // Supprimer le fichier temporaire
        Storage::delete($this->tempPath);

        // $qrCodePath = '../app/qrcodes/test_qrcode.png';
        // QrCode::format('png')->size(300)->generate($text, $qrCodePath);
        //    $qrCodePath = $qrCodeGenerator->generateQRCode($this->user->telephone);

    }
}


// namespace App\Jobs;

// use App\Services\Contracts\IUploadService;
// use BaconQrCode\Encoder\QrCode;
// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Queue\SerializesModels;
// use Illuminate\Support\Facades\Storage;
// //  use Symfony\Component\HttpFoundation\File\UploadedFile;
//  use Illuminate\Http\UploadedFile;

// class StoreImageInCloud implements ShouldQueue
// {
  
//     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//     protected $client;
//     protected $tempPath;

//     public function __construct($client, $tempPath)
//     {
//         $this->client = $client;
//         $this->tempPath = $tempPath;
       
//     }

//     public function handle(IUploadService $uploadService)
//     {
//         // Convertir le chemin temporaire en instance UploadedFile
//         $file = new UploadedFile(Storage::path($this->tempPath), basename($this->tempPath));

//         // Télécharger le fichier vers le cloud
//         $fileUrl = $uploadService->upload($file);
       

//         // Mettre à jour l'utilisateur avec l'URL du fichier
//         $this->client->user->update(['photo' => $fileUrl]);

//         // Supprimer le fichier temporaire
//         Storage::delete($this->tempPath);

//         // $qrCodePath = '../app/qrcodes/test_qrcode.png';
//         // QrCode::format('png')->size(300)->generate($text, $qrCodePath);
//         //    $qrCodePath = $qrCodeGenerator->generateQRCode($this->user->telephone);

//     }
// }

// use Throwable;
// use Illuminate\Support\Facades\Log;

// class StoreImageInCloud implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//     protected $user;
//     protected $tempPath;

//     // Exécuter le job une seule fois
//     public $tries = 1;

//     public function __construct($user, $tempPath)
//     {
//         $this->user = $user;
//         $this->tempPath = $tempPath;
//     }

//     public function handle(IUploadService $uploadService)
//     {
//         try {
//             // Convertir le chemin temporaire en instance UploadedFile
//             $file = new UploadedFile(Storage::path($this->tempPath), basename($this->tempPath));

//             // Télécharger le fichier vers le cloud
//             $fileUrl = $uploadService->upload($file);

//             // Mettre à jour l'utilisateur avec l'URL du fichier dans le cloud
//             $this->user->update(['photo' => $fileUrl]);

//             // Supprimer le fichier temporaire
//             Storage::delete($this->tempPath);

//         } catch (Throwable $e) {
//             // Si l'upload échoue, enregistrer le fichier localement
//             $this->handleFailure($e);
//         }
//     }

//     protected function handleFailure(Throwable $e)
//     {
//         // Enregistrer un message d'erreur dans le log
//         Log::error('Échec du téléchargement vers le cloud : ' . $e->getMessage());

//         // Déplacer le fichier vers un répertoire local sous "public/storage/photos"
//         $localDirectory = 'public/photos/';
//         $localPath = $localDirectory . basename($this->tempPath);

//         // Assurez-vous que le dossier existe
//         if (!Storage::exists($localDirectory)) {
//             Storage::makeDirectory($localDirectory);
//         }

//         // Sauvegarder l'image localement
//         Storage::move($this->tempPath, $localPath);

//         // Mettre à jour l'utilisateur avec l'URL locale
//         $photoUrl = Storage::url($localPath);  // Générer l'URL publique
//         $this->user->update(['photo' => $photoUrl]);

//         // Enregistrer l'URL dans la base de données
//         Log::info('Image sauvegardée localement à : ' . $photoUrl);
//     }
// }

