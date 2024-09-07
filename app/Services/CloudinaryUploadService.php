<?php

namespace App\Services;

use App\Services\Contracts\IUploadService;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudinaryUploadService implements IUploadService
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

    public function upload($file): string
    {
        if ($file instanceof UploadedFile) {
            $realPath = $file->getRealPath();
        } elseif (is_string($file)) {
            $realPath = $file;
        } else {
            throw new \Exception('Type de fichier non supporté');
        }

        $result = $this->cloudinary->uploadApi()->upload($realPath);
        return $result['secure_url'];
    }
}
