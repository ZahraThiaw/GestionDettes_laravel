<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ImageUploaded
{
    use Dispatchable, SerializesModels;

    public $user;
    public $file;

    public function __construct(User $user, $file)
    {
        $this->user = $user;
        $this->file = $file;
    }
}
