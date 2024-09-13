<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ArticleRepository;
use App\Repositories\ArticleRepositoryImpl;
use App\Repositories\ClientRepository;
use App\Repositories\ClientRepositoryInterface;
use App\Repositories\DetteRepository;
use App\Repositories\DetteRepositoryInterface;
use App\Services\ArticleService;
use App\Services\ArticleServiceImpl;
use App\Services\ClientService;
use App\Services\ClientServiceInterface;
use App\Services\CloudinaryUploadService;
use App\Services\Contracts\IDebtArchivingService;
use App\Services\Contracts\ILoyaltyCardService;
use App\Services\Contracts\IQrCodeService;
// use App\Services\CloudUploadService;
use App\Services\Contracts\IUploadService;
use App\Services\Contracts\SmsServiceInterface;
use App\Services\DebtArchivingService;
use App\Services\DetteService;
use App\Services\DetteServiceInterface;
use App\Services\FirebaseArchivingService;
use App\Services\LoyaltyCardService;
use App\Services\QrCodeService;
use App\Notifications\Channels\SmsChannel;
use App\Services\SmsService;
use App\Services\TwilioSmsService;
use Illuminate\Support\Facades\Notification;

// use App\Services\UploadService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ArticleRepository::class, ArticleRepositoryImpl::class);
        $this->app->bind(ArticleService::class, ArticleServiceImpl::class);

        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(ClientServiceInterface::class, ClientService::class);

        //$this->app->bind(IDebtArchivingService::class, DebtArchivingService::class);
        //$this->app->bind(IDebtArchivingService::class, FirebaseArchivingService::class);


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


        // Lier l'interface DetteRepository à son implémentation
        $this->app->bind(DetteRepositoryInterface::class, DetteRepository::class);

        // Lier l'interface DetteService à son implémentation
        $this->app->bind(DetteServiceInterface::class, DetteService::class);

        // // Liaison de l'interface SmsServiceInterface à une implémentation
        // $this->app->singleton(SmsServiceInterface::class, function ($app) {
        //     $smsService = env('SMS_SERVICE', 'twilio');

        //     switch ($smsService) {
        //         case 'infobip':
        //             return new SmsService();
        //         case 'twilio':
        //             return new TwilioSmsService();
        //         default:
        //             throw new \Exception("Service SMS inconnu : $smsService");
        //     }
        // });
    }

    public function boot()
    {
        //
        // // Enregistrer l'observer pour User
        // User::observe(UserObserver::class);

        // Notification::extend('sms', function ($app) {
        //     return new SmsChannel($app->make(SmsServiceInterface::class));
        // });
    }
}

