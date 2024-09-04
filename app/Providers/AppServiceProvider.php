<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ArticleRepository;
use App\Repositories\ArticleRepositoryImpl;
use App\Repositories\ClientRepository;
use App\Repositories\ClientRepositoryInterface;
use App\Services\ArticleService;
use App\Services\ArticleServiceImpl;
use App\Services\ClientService;
use App\Services\CloudinaryUploadService;
use App\Services\CloudUploadService;
use App\Services\UploadService;
use App\Services\UploadServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ArticleRepository::class, ArticleRepositoryImpl::class);
        $this->app->bind(ArticleService::class, ArticleServiceImpl::class);

        // Enregistrer le ClientRepositoryInterface avec son implémentation
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);

        // Enregistrer ClientService avec un alias
        $this->app->singleton('ClientService', function ($app) {
            return new ClientService(
                $app->make(ClientRepositoryInterface::class),
                $app->make(UploadService::class)
            );
        });

        // Enregistrer CloudUploadService pour UploadServiceInterface
        $this->app->bind(UploadServiceInterface::class, CloudinaryUploadService::class);
        $this->app->bind(UploadServiceInterface::class, UploadService::class);

        $this->app->bind(UploadServiceInterface::class, function () {
            return config('app.upload_service') === 'cloudinary'
                ? new CloudinaryUploadService()
                : new UploadService();
        });
    }

    public function boot()
    {
        //
    }
}

