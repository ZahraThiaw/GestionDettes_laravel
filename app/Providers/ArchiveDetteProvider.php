<?php

namespace App\Providers;

use App\Services\Contracts\IDebtArchivingService;
use Illuminate\Support\ServiceProvider;
use App\Services\DebtArchivingService;
use App\Services\FirebaseArchivingService;

class ArchiveDetteProvider extends ServiceProvider
{
    /**
     * Enregistre les services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton(IDebtArchivingService::class, function ($app) {
            $service = env('ARCHIVE_SERVICE', 'firebase');  // Default to 'firebase'

            switch ($service) {
                case 'firebase':
                    return new FirebaseArchivingService();  // Assurez-vous que cette classe est correctement incluse
                case 'mongodb':
                    return new DebtArchivingService();  // Assurez-vous que cette classe est correctement incluse
                default:
                    throw new \Exception("Service archivage inconnu : $service");
            }
        });     

    }
}
