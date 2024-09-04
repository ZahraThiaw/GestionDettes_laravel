<?php

namespace App\Services;

use App\Services\UploadServiceInterface;
use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Transformation;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudinaryUploadService implements UploadServiceInterface
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    public function uploadImage($imageFile)
    {
        if ($imageFile instanceof UploadedFile) {
            $realPath = $imageFile->getRealPath();
        } elseif (is_string($imageFile)) {
            $realPath = $imageFile;
        } else {
            throw new \Exception('Type de fichier non supporté');
        }

        // Upload the image to Cloudinary
        $result = $this->cloudinary->uploadApi()->upload($realPath);

        // Return the secure URL of the uploaded image
        return $result['secure_url'];
    }
}
