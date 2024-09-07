<?php

namespace App\Services\Contracts;

use App\Models\Client;

interface ILoyaltyCardService
{
    public function generateLoyaltyCard(Client $client);
}
