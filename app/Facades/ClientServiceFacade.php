<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ClientServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ClientService'; // Assurez-vous que ce nom correspond à celui enregistré
    }
}
