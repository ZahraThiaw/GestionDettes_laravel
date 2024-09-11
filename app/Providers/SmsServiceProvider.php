<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\SmsServiceInterface;
use App\Services\SmsService;
use App\Services\TwilioSmsService;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services.
     *
     * @return void
     */
    public function register()
    {
        // Liaison de l'interface à l'implémentation
        //$this->app->bind(SmsServiceInterface::class, SmsService::class);
        //$this->app->bind(SmsServiceInterface::class, TwilioSmsService::class);

        $this->app->singleton(SmsServiceInterface::class, function ($app) {
            $smsService = env('SMS_SERVICE', 'twilio');  // 'twilio' est la valeur par défaut

            switch ($smsService) {
                case 'infobip':
                    return new SmsService();  // Assurez-vous que cette classe est correctement incluse
                case 'twilio':
                    return new TwilioSmsService();  // Assurez-vous que cette classe est correctement incluse
                default:
                    throw new \Exception("Service SMS inconnu : $smsService");
            }
        });

    }
}
