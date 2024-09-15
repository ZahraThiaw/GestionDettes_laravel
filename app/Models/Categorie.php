<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    // Le nom de la table associée
    protected $table = 'categories';

    // Les attributs qui peuvent être mass assignables
    protected $fillable = ['libelle'];

    /**
     * Relation One-to-Many avec Client.
     * Une catégorie peut avoir plusieurs clients.
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
