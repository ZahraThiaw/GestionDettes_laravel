<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UploadFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'uploadservice'; // clé qui sera utilisée pour accéder au service
    }
}
