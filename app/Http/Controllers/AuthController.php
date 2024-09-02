<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="API Documentation",
 *         version="1.0.0",
 *         description="Documentation de l'API pour le projet"
 *     ),
 *     @OA\Server(
 *         url="http://127.0.0.1:8000/wane/v1",
 *         description="Serveur API de développement"
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer",
 *             bearerFormat="JWT",
 *             description="JWT authorization header using the Bearer scheme"
 *         )
 *     )
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login a user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="login", type="string", example="user@example.com"),
     *                 @OA\Property(property="password", type="string", example="password123"),
     *                 required={"login", "password"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTYyMzkwMjIyfQ.S5_UZmA1W3vFhbFzg0_8GeP5bnA63R7a5yBzh7V7QnY")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}},
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Logout a user",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}},
     * )
     */
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
