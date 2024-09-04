<?php

// app/Services/UploadService.php
namespace App\Services;

use Illuminate\Support\Facades\Storage;

class UploadService implements UploadServiceInterface
{
    public function uploadImage($imageFile)
    {
        // Assurez-vous que le fichier est une image
        if (!$imageFile->isValid() || !in_array($imageFile->extension(), ['jpg', 'jpeg', 'png', 'svg'])) {
            throw new \Exception('Invalid image file.');
        }

        // Définir le chemin de stockage
        $path = $imageFile->store('images', 'public');

        // Lire le contenu du fichier et l'encoder en base64
        $fileContent = Storage::disk('public')->get($path);
        $base64Encoded = base64_encode($fileContent);

        return $base64Encoded;
    }
}
