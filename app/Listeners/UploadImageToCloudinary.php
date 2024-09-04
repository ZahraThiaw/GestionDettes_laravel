<?php

namespace App\Listeners;

use App\Events\ImageUploadEvent;
use App\Services\CloudinaryUploadService;

class UploadImageToCloudinary
{
    protected $uploadService;

    public function __construct(CloudinaryUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function handle(ImageUploadEvent $event)
    {
        $this->uploadService->uploadImage($event->imageFile);
    }
}

