<?php

namespace App\Jobs;

use App\Services\Contracts\IUploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RetryImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $localPath;

    public function __construct(User $user, $localPath)
    {
        $this->user = $user;
        $this->localPath = $localPath;
    }

    public function handle(IUploadService $uploadService)
    {
        try {
            // Convertir le chemin local en instance UploadedFile
            $file = new \Illuminate\Http\UploadedFile(Storage::path($this->localPath), basename($this->localPath));

            // Télécharger à nouveau le fichier vers le cloud
            $fileUrl = $uploadService->upload($file);

            // Mettre à jour l'utilisateur avec l'URL du fichier
            $this->user->update(['photo' => $fileUrl]);

            // Supprimer le fichier local
            Storage::delete($this->localPath);

            Log::info("L'image de l'utilisateur {$this->user->id} a été renvoyée vers le cloud avec succès.");

        } catch (\Exception $e) {
            // Enregistrer l'échec dans les logs
            Log::error("Échec du renvoi de l'image pour l'utilisateur {$this->user->id}: " . $e->getMessage());
        }
    }
}
