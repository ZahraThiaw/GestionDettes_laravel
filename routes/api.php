<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DetteController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('v1/users', function () {
    return \App\Models\User::all();
});

Route::get('v1/users/{id}', function ($id) {
    return \App\Models\User::findOrFail($id);
});


Route::prefix('v1')->group(function () { 
    Route::post('users', [UserController::class, 'store']); 
    Route::put('users/{id}', [UserController::class, 'update']); 
    Route::delete('users/{id}', [UserController::class, 'destroy']); });


// // Définir un groupe de routes avec un préfixe "api/v1"
// Route::prefix('v1')->group(function () {
//     Route::get('clients', [ClientController::class, 'index']);
//     Route::get('clients/paginate', [ClientController::class, 'indexWithPagination']);
//     Route::get('clients/{id}', [ClientController::class, 'show']);
//     Route::get('clients/{id}/eager', [ClientController::class, 'showWithEager']);
//     Route::post('clients', [ClientController::class, 'store']);
//     Route::put('clients/{id}', [ClientController::class, 'update']);
//     Route::patch('clients/{id}', [ClientController::class, 'update']);
//     Route::delete('clients/{id}', [ClientController::class, 'destroy']);
// });


Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Définir un groupe de routes avec un préfixe "api/v1"
Route::prefix('v1')->middleware(['auth:sanctum', 'role:Admin,Boutiquier'])->group(function () {
    Route::get('clients', [ClientController::class, 'index']);
    Route::post('clients/telephone', [ClientController::class, 'filterByTelephone']);
    Route::get('clients/{id}', [ClientController::class, 'show']);
    Route::post('clients/{id}/user', [ClientController::class, 'showClientWithUser']);
    Route::get('clients/{id}/eager', [ClientController::class, 'showWithEager']);
    Route::post('clients', [ClientController::class, 'store']);
    Route::put('clients/{id}', [ClientController::class, 'update']);
    Route::patch('clients/{id}', [ClientController::class, 'update']);
    Route::delete('clients/{id}', [ClientController::class, 'destroy']);

    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{id}', [ArticleController::class, 'show']);
    Route::post('articles/libelle', [ArticleController::class, 'filterByLibelle']);
    Route::post('articles', [ArticleController::class, 'store']);
    Route::put('articles/{id}', [ArticleController::class, 'update']);
    Route::patch('articles/{id}', [ArticleController::class, 'update']);
    Route::post('articles/stock', [ArticleController::class, 'updateStock']);
    Route::delete('articles/{id}', [ArticleController::class, 'destroy']); // Soft Delete
    Route::post('articles/{id}/restore', [ArticleController::class, 'restore']); // Restore Soft Deleted Article

    Route::post('clients/{id}/dettes', [DetteController::class, 'getClientDettes']);
});
