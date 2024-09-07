<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCreated
{
    use Dispatchable, SerializesModels;

    public $user;
    public $photoPath;

    public function __construct(User $user, $photoPath)
    {
        $this->user = $user;
        $this->photoPath = $photoPath;
    }
}