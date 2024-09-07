<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ArticleRepository;
use App\Repositories\ArticleRepositoryImpl;
use App\Repositories\ClientRepository;
use App\Repositories\ClientRepositoryInterface;
use App\Services\ArticleService;
use App\Services\ArticleServiceImpl;
use App\Services\ClientService;
use App\Services\ClientServiceInterface;
use App\Services\CloudinaryUploadService;
use App\Services\Contracts\ILoyaltyCardService;
use App\Services\Contracts\IQrCodeService;
// use App\Services\CloudUploadService;
use App\Services\Contracts\IUploadService;
use App\Services\LoyaltyCardService;
use App\Services\QrCodeService;

// use App\Services\UploadService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ArticleRepository::class, ArticleRepositoryImpl::class);
        $this->app->bind(ArticleService::class, ArticleServiceImpl::class);

        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(ClientServiceInterface::class, ClientService::class);

        // Enregistrer ClientService avec un alias
        // $this->app->singleton('ClientService', function ($app) {
        //     return new ClientService(
        //         $app->make(ClientRepositoryInterface::class),
        //     );
        // });

        // $this->app->singleton('uploadservice', function ($app) {
        //     return new UploadService();
        // });

        // $this->app->singleton(IUploadService::class, UploadService::class);
        // $this->app->alias(IUploadService::class, 'uploadService');

        $this->app->singleton(IUploadService::class, CloudinaryUploadService::class);
        $this->app->singleton(IQrCodeService::class, QrCodeService::class);
        $this->app->singleton(ILoyaltyCardService::class, LoyaltyCardService::class);
    }

    // public function boot()
    // {
    //     //
    //     // Enregistrer l'observer pour User
    //     User::observe(UserObserver::class);
    // }
}

