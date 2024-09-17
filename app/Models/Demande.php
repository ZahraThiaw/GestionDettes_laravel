<?php

namespace App\Models;

use App\Enums\StatutDemande;
use Illuminate\Database\Eloquent\Model;

// class Demande extends Model
// {
//     protected $table = 'demandes_dettes';

//     protected $fillable = [
//         'date',
//         'client_id',
//         'status', // Statut de la demande (en attente, approuvée, refusée)
//     ];

//     protected $hidden = [
//         'created_at',
//         'updated_at',
//     ];

//     protected $casts = [
//         'status' => StatutDemande::class, // Conversion du statut en énumération
//     ];

//     public function client()
//     {
//         return $this->belongsTo(Client::class);
//     }

//     public function articles()
//     {
//         return $this->belongsToMany(Article::class, 'demande_articles')
//                     ->withPivot('qte')
//                     ->withTimestamps();
//     }
// }

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Demande extends Model
{
    protected $table = 'demandes_dettes';

    protected $fillable = [
        'date',
        'client_id',
        'status', // Statut de la demande (en attente, approuvée, refusée)
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Cast 'date' as a date object
    protected $casts = [
        'date' => 'datetime', // Assure que 'date' est traité comme un objet date
        'status' => StatutDemande::class, // Conversion du statut en énumération
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'demande_articles')
                    ->withPivot('qte')
                    ->withTimestamps();
    }
}


