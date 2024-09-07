<?php

// namespace App\Events;

// use Illuminate\Foundation\Events\Dispatchable;
// use Illuminate\Queue\SerializesModels;
// use App\Models\Client;

// class ClientCreated
// {
//     use Dispatchable, SerializesModels;

//     public $client;

//     public function __construct(Client $client)
//     {
//         $this->client = $client;
//     }
// }


namespace App\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoyaltyCardGenerated
{
    use Dispatchable, SerializesModels;

    public $client;
    public $loyaltyCardPath;

    public function __construct(Client $client, $loyaltyCardPath)
    {
        $this->client = $client;
        $this->loyaltyCardPath = $loyaltyCardPath;
    }
}
