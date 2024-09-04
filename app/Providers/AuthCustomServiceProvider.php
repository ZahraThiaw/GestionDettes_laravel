<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AuthentificationPassport;
use App\Services\AuthentificationServiceInterface;

class AuthCustomServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Utiliser Passport par défaut
        $this->app->bind(AuthentificationServiceInterface::class, AuthentificationPassport::class);
    }

    public function boot()
    {
        // Aucun code spécifique ici pour le moment
    }
}
