<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        //
        $this->registerPolicies();

        // Enregistre les routes de Passport
        Passport::ignoreRoutes();

        // Utilisation des Gate pour gérer les autorisations
        Gate::define('isAdmin', [UserPolicy::class, 'isAdmin']);
        Gate::define('isBoutiquier', [UserPolicy::class, 'isBoutiquier']);
        Gate::define('isClient', [UserPolicy::class, 'isClient']);
    }
}
