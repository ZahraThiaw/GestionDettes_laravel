<?php

namespace App\Exceptions;

use Exception;

class ServiceException extends Exception
{
    public function __construct($message = "Erreur dans le service", $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
