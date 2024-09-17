<?php

namespace App\Enums;

enum StatutDemande: string
{
    case EN_COURS = 'En cours';
    case VALIDEE = 'Validée';
    case ANNULEE = 'Annulée';

    public function label(): string
    {
        return match ($this) {
            self::EN_COURS => 'En cours',
            self::VALIDEE => 'Validée',
            self::ANNULEE => 'Annulée',
        };
    }
}

