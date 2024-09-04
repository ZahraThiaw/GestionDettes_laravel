<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\FilterByTelephoneScope;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'surnom',
        'telephone',
        'adresse',
        'user_id',
    ];

    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new FilterByTelephoneScope(request()->input('telephone')));
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
}
