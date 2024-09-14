<?php

namespace App\Services;

use HTTP_Request2;
use App\Models\Dette;
use App\Services\Contracts\SmsServiceInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class SmsService implements SmsServiceInterface
{
    protected $apiUrl = 'https://xl3mw4.api.infobip.com/sms/2/text/advanced';
    protected $apiKey = '84b7ddeecb7281ca14c6e5ed43863852-bdfdbd3b-20aa-4dae-a647-8f3e2c938cc6';
    protected $fromNumber = '447491163443';

    public function sendSmsToClient($toPhoneNumber, $montantRestant, $clientName, $message)
    {
        //$message = "Cher(e) $clientName, vous avez un montant restant de $montantRestant FCFA à régler. Merci de procéder au paiement.";

        $request = new HTTP_Request2();
        $request->setUrl($this->apiUrl);
        $request->setMethod(HTTP_Request2::METHOD_POST);
        $request->setConfig(['follow_redirects' => true]);

        $request->setHeader([
            'Authorization' => 'App ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        $request->setBody(json_encode([
            'messages' => [
                [
                    'destinations' => [['to' => '221'.$toPhoneNumber]],
                    'from' => $this->fromNumber,
                    'text' => $message,
                ]
            ]
        ]));

        try {
            $response = $request->send();
            if ($response->getStatus() == 200) {
                Log::info("SMS envoyé à $toPhoneNumber: " . $message);
            } else {
                $errorMessage = $response->getReasonPhrase();
                Log::error("Échec de l'envoi du SMS à $toPhoneNumber: $errorMessage");
                throw new Exception("Échec de l'envoi: $errorMessage");
            }
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'envoi du SMS: ' . $e->getMessage());
            throw $e;
        }
    }
}
