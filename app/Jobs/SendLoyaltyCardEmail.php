<?php

namespace App\Jobs;

use App\Mail\ClientLoyaltyCardMail;
use App\Models\Client;
use App\Mail\SendLoyaltyCard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLoyaltyCardEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $client;
    protected $loyaltyCardPath;

    public function __construct(Client $client, $loyaltyCardPath)
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