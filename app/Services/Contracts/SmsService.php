<?php

namespace App\Services;

use App\Services\Contracts\ISmsService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SmsService implements ISmsService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        // Initialiser le client HTTP et charger les configurations Infobip
        $this->client = new Client();
        $this->apiKey = env('INFOBIP_API_KEY');
        $this->baseUrl = env('INFOBIP_BASE_URL');
    }

    /**
     * Envoie un SMS à un numéro de téléphone donné avec un message.
     *
     * @param string $phoneNumber
     * @param string $message
     * @return void
     */
    public function sendSms($phoneNumber, $message)
    {
        try {
            // Préparer les données pour l'API Infobip
            $payload = [
                'messages' => [
                    [
                        'from' => env('INFOBIP_SENDER_ID'), // L'expéditeur
                        'destinations' => [
                            [
                                'to' => $phoneNumber, // Le numéro du destinataire
                            ],
                        ],
                        'text' => $message, // Le message à envoyer
                    ],
                ],
            ];

            // Envoyer la requête POST à Infobip
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'Authorization' => 'App ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            // Vérifier la réponse
            if ($response->getStatusCode() === 200) {
                Log::info("SMS envoyé avec succès à {$phoneNumber}");
            } else {
                Log::error("Erreur lors de l'envoi du SMS : " . $response->getBody());
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi du SMS via Infobip : " . $e->getMessage());
        }
    }
}
