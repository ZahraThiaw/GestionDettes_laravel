<?php
namespace App\Services;

use Twilio\Rest\Client;
use App\Models\Dette;
use App\Services\Contracts\SmsServiceInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class TwilioSmsService implements SmsServiceInterface
{
    protected $twilioClient;
    protected $twilioPhoneNumber;

    public function __construct()
    {
        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $this->twilioPhoneNumber = env('TWILIO_PHONE_NUMBER');

        // Initialiser le client Twilio
        $this->twilioClient = new Client($accountSid, $authToken);
    }

    public function sendSmsToClient($toPhoneNumber, $montantRestant, $clientName, $message)
    {
        //$message = "Cher(e) $clientName, vous avez un montant restant de $montantRestant FCFA à régler. Merci de procéder au paiement.";

        try {
            $this->twilioClient->messages->create(
                '+221' . $toPhoneNumber, // To phone number with country code
                [
                    'from' => $this->twilioPhoneNumber,
                    'body' => $message,
                ]
            );
            Log::info("SMS envoyé à $toPhoneNumber: " . $message);
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'envoi du SMS: ' . $e->getMessage());
            throw $e;
        }
    }
}
