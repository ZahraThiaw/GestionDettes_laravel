<?php

namespace App\Jobs;

use App\Services\Contracts\IUploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
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
    }
}
