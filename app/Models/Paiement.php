<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    // La table associée à ce modèle
    protected $table = 'paiements';

    // Les colonnes qui peuvent être modifiées
    protected $fillable = [
        'montant',
        'date',
        'dette_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $guarded = [
        'id', // La clé primaire ne peut pas être modifiée
    ];

    /**
     * Relation avec le modèle Dette.
     * Un paiement est associé à une dette.
     */
    public function dette()
    {
        return $this->belongsTo(Dette::class);
    }
}
