<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle', 
        'prix', 
        'qteStock'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $guarded = [
        'id', // La clé primaire ne peut pas être modifiée
    ];

    
    // Méthode pour gérer le filtrage des articles
    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['disponible'])) {
            if ($filters['disponible'] === 'oui') {
                $query->where('qteStock', '>', 0);
            } elseif ($filters['disponible'] === 'non') {
                $query->where('qteStock', '=', 0);
            }
        }

        if (isset($filters['libelle'])) {
            $query->where('libelle', 'LIKE', '%' . $filters['libelle'] . '%');
        }

        return $query;
    }
}
