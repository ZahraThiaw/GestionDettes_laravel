<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImageUploadEvent
{
    use Dispatchable, SerializesModels;

    public $imageFile;

    public function __construct($imageFile)
    {
        $this->imageFile = $imageFile;
    }
}

