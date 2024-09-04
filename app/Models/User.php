<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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

    
    // /**
    //  * Crée un nouveau token avec des claims personnalisés.
    //  *
    //  * @param  string  $tokenName
    //  * @return string
    //  */
    // public function createTokenWithClaims($tokenName)
    // {
    //     // Création du token
    //     $token = $this->createToken($tokenName)->accessToken;

    //     // Ajouter des claims personnalisés
    //     $server = app(AuthorizationServer::class);
    //     $accessToken = $server->getTokenRepository()->find($token);

    //     $accessToken->setClaims([
    //         'nom' => $this->nom,
    //         'prenom' => $this->prenom,
    //         'login' => $this->login,
    //         'role_id' => $this->role_id,
    //         'photo' => $this->photo,
    //     ]);

    //     return $token;
    // }
}
