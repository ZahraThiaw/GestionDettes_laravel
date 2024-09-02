<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('login', 'password');

        if (Auth::attempt($credentials)) {
            /**  @var \App\Models\User $user **/
            $user = Auth::user();
            $token = $user->createToken('Personal Access Token')->accessToken;
            return response()->json([
                'token' => $token
            ]);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function logout(Request $request)
    {
         /**  @var \App\Models\User $user **/
        $user = Auth::user();
        // Révoquer le jeton d'accès actuel
        $user->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
