<?php

namespace App\Models;

use App\Events\ClientCreated;
use App\Jobs\SendLoyaltyCardEmail;
use App\Observers\ClientObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\FilterByTelephoneScope;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    use HasFactory , Notifiable;

    protected $fillable = [
        'surnom',
        'telephone',
        'adresse',
        'user_id',
        'qrcode',
        'categorie_id',
        "max_montant",
    ];

    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    //#[ObservedBy([ClientObserver::class])]

    protected static function booted()
    {
        static::addGlobalScope(new FilterByTelephoneScope(request()->input('telephone')));

        // Enregistrer l'observateur
        //static::observe(ClientObserver::class);
        //dd('ok');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation : Un client peut avoir plusieurs dettes
    public function dettes()
    {
        return $this->hasMany(Dette::class);
    }

    // Méthode pour vérifier si le client a un compte actif
    public function hasActiveAccount()
    {
        return $this->user && $this->user->active; // Si le client a un utilisateur associé et si son compte est actif
    }  

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at'); // Retourne le constructeur de requêtes
    }

    public function readNotifications()
    {
        return $this->notifications()->whereNotNull('read_at'); // Retourne le constructeur de requêtes
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }


}
