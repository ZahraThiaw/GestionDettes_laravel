<?php

// namespace App\Jobs;

// use App\Mail\LoyaltyCardMail;
// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Queue\SerializesModels;
// use Illuminate\Support\Facades\Mail;

// class SendLoyaltyCardEmail implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//     protected $client;

//     public function __construct($client)
//     {
//         $this->client = $client;
//     }

//     public function handle()
//     {
//         // Envoi de l'e-mail avec la carte de fidélité en pièce jointe
//         Mail::to($this->client->user->email)->send(new LoyaltyCardMail($this->client));
//     }
// }


namespace App\Jobs;

use App\Mail\ClientLoyaltyCardMail;
use App\Models\Client;
use App\Services\Contracts\ILoyaltyCardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLoyaltyCardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $client;
    protected $loyaltyCardPath;

    public function __construct(Client $client, ILoyaltyCardService $loyaltyCardPath)
    {
        $this->client = $client;
        $this->loyaltyCardPath = $loyaltyCardPath;
    }

    public function handle()
    {
        // Envoyer l'email avec la carte de fidélité en pièce jointe
        Mail::to($this->client->user->login)->send(new ClientLoyaltyCardMail($this->client, $this->loyaltyCardPath));
    }
}
