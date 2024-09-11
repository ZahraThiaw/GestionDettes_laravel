<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\SmsServiceInterface;
use App\Services\SmsService;

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
        $this->app->bind(SmsServiceInterface::class, SmsService::class);
    }
}
