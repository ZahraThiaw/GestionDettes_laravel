<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\AuthentificationServiceInterface;

class AuthController extends Controller
{

    protected $authService;

    // Injecter l'interface dans le constructeur
    public function __construct(AuthentificationServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $credentials = $request->only('login', 'password');
        $token = $this->authService->authentificate($credentials);

        if ($token) {
            return ['token' => $token];
        }

        return [
            'statut' => 'Echec',
            'message' => 'Unauthorized',
            'httpStatus' => 403
        ];  
    }

    public function logout()
    {
        $this->authService->logout();
        return [
            'statut' => 'Success',
            'message' => 'Successfully logged out',
            'httpStatus' => 200
        ];
    }
}
