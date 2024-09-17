<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Jobs\SendLoyaltyCardJob;
use App\Jobs\StoreImageInCloud;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\AuthorizationServer;



class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'login',
        'password',
        'role_id', 
        'photo',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    protected $guarded = [
        'id', // La clé primaire ne peut pas être modifiée
    ];

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    protected static function booted()
    {
        // Enregistrer l'observateur
        static::observe(UserObserver::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at'); // Retourne le constructeur de requêtes
    }
}
