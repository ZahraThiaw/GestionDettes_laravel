<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientLoyaltyCardMail extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $pdfPath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($client, $pdfPath)
    {
        $this->client = $client;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->view('emails.client_loyalty_card')
                    ->with([
                        'clientSurname' => $this->client->user->prenom,
                        'clientName' => $this->client->user->nom
                    ])
                    ->attach($this->pdfPath, [
                        'as' => 'CarteFidélitéClient.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }

}
