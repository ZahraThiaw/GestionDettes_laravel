<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dette extends Model
{
    use HasFactory;

    // La table associée à ce modèle
    protected $table = 'dettes';

    // Les colonnes qui peuvent être modifiées
    protected $fillable = [
        'date',
        'montant',
        'client_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $guarded = [
        'id', // La clé primaire ne peut pas être modifiée
    ];

    protected $casts = [
        'date_echeance' => 'datetime', // Ceci convertira date_echeance en Carbon
    ];

    /**
     * Relation avec le modèle Client.
     * Une dette appartient à un client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation avec le modèle Paiement.
     * Une dette peut avoir plusieurs paiements.
     */
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    /**
     * Relation avec les articles via la table pivot article_dette.
     */
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_dettes')
                    ->withPivot('qteVente', 'prixVente')
                    ->withTimestamps();
    }
}
