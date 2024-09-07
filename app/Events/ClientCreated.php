<?php

namespace App\Events;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientCreated
{
    use Dispatchable, SerializesModels;
    public $client;
    public $photoFilePath;
    public $loyaltyCardPath;

    public function __construct(Client $client,  $photoFilePath, $loyaltyCardPath)
    {
        $this->client = $client;
        $this->photoFilePath = $photoFilePath;
        $this->loyaltyCardPath = $loyaltyCardPath;
    }
}