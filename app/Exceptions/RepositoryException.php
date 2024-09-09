<?php

namespace App\Exceptions;

use Exception;

class RepositoryException extends Exception
{
    public function __construct($message = "Erreur dans le repository", $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}