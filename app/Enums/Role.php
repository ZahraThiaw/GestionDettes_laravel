<?php

namespace App\Enums;

enum Role: string
{
    case Boutiquier = 'Boutiquier';
    case Admin = 'Admin';
    case Client = 'Client';
}
