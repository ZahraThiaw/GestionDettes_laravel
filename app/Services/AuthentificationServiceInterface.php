<?php

namespace App\Services;

interface AuthentificationServiceInterface
{
    public function authentificate(array $credentials);
    public function logout();
}
