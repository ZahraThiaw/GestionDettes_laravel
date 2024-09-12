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

    public function sendDebtReminderToClients()
    {
        // Récupérer toutes les dettes avec leurs paiements et clients
        $dettes = Dette::with('paiements', 'client')->get();

        // Regrouper les dettes par client
        $clients = [];
        foreach ($dettes as $dette) {
            $totalPaiements = $dette->paiements->sum('montant');
            $montantRestant = $dette->montant - $totalPaiements;

            if ($montantRestant > 0) {
                $clientId = $dette->client->id;
                if (!isset($clients[$clientId])) {
                    $clients[$clientId] = [
                        'client' => $dette->client,
                        'montantRestant' => 0,
                    ];
                }
                $clients[$clientId]['montantRestant'] += $montantRestant;
            }
        }

        // Envoyer un SMS par client avec le montant total restant
        foreach ($clients as $clientId => $clientData) {
            $client = $clientData['client'];
            $montantRestant = $clientData['montantRestant'];
            $clientPhoneNumber = $client->telephone;
            $clientName = $client->surnom;

            try {
                $this->sendSmsToClient($clientPhoneNumber, $montantRestant, $clientName);
            } catch (Exception $e) {
                // Log the error if sending the SMS fails
                Log::error("Erreur lors de l'envoi du SMS au client $clientName ($clientPhoneNumber) : " . $e->getMessage());
            }
        }
    }

    public function sendSmsToClient($toPhoneNumber, $montantRestant, $clientName)
    {
        $message = "Cher(e) $clientName, vous avez un montant restant de $montantRestant FCFA à régler. Merci de procéder au paiement.";

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
