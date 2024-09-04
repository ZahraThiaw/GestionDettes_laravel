<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CloudUploadService;
use App\Services\UploadService;
use App\Services\UploadServiceInterface;

class UploadServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Enregistrer l'implémentation CloudUploadService pour UploadServiceInterface
        //$this->app->bind(UploadServiceInterface::class, CloudUploadService::class);

        // Si vous avez une autre implémentation, vous pouvez l'enregistrer ici aussi
        // $this->app->bind(UploadServiceInterface::class, UploadService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
