<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dette extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 
        'montant', 
        'montantDu', 
        'montantRestant', 
        'client_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $guarded = [
        'id', // La clé primaire ne peut pas être modifiée
    ];

    // Relation : Une dette appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
}

