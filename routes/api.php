<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DetteController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailTestController;
use App\Http\Controllers\NotificationController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::get('v1/users', function () {
//     return \App\Models\User::all();
// });

// Route::get('v1/users/{id}', function ($id) {
//     return \App\Models\User::findOrFail($id);
// });


// Route::prefix('v1')->group(function () { 
//     Route::post('users', [UserController::class, 'store']); 
//     Route::put('users/{id}', [UserController::class, 'update']); 
//     Route::delete('users/{id}', [UserController::class, 'destroy']);
//  });


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


// Route::post('login', [AuthController::class, 'login']);
// Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Routes accessibles uniquement par les Admins
        Route::middleware('can:isAdmin')->group(function (){
            Route::post('/users', [UserController::class, 'store']);
            Route::get('/users', [UserController::class, 'index']);

            Route::get('dettes/archive', [DebtController::class, 'getArchivedDebts']);
            Route::get('/archive/clients/{clientId}/dettes', [DebtController::class, 'getArchivedDebtsByClient']);
            Route::get('archive/dettes/{debtId}', [DebtController::class, 'getArchivedDebtById']);
            Route::get('restaure/{date}', [DebtController::class, 'restoreArchivedDebtsByDate']);
            Route::get('restaure/dette/{debtId}', [DebtController::class, 'restoreArchivedDebt']);
            Route::get('restaure/client/{clientId}', [DebtController::class, 'restoreArchivedDebtsByClient']);
        });

        // Routes accessibles uniquement par les Admins
        Route::middleware('can:isBoutiquier')->group(function (){
            //Route::get('articles', [ArticleController::class, 'index']);
            Route::get('articles', [ArticleController::class, 'index']);
            Route::post('articles', [ArticleController::class, 'store']);
            Route::patch('articles/{id}', [ArticleController::class, 'update']);
            Route::get('articles/{id}', [ArticleController::class, 'show']);
            Route::put('articles/{id}', [ArticleController::class, 'update']);
            Route::delete('articles/{id}', [ArticleController::class, 'destroy']); // Soft Delete
            Route::post('articles/restore/{id}', [ArticleController::class, 'restore']); // Restore Soft Deleted Article
            Route::post('articles/stock', [ArticleController::class, 'updateStock']);
            Route::post('articles/libelle', [ArticleController::class, 'filterByLibelle']);

            Route::post('clients', [ClientController::class, 'store']);
            Route::get('clients', [ClientController::class, 'index']);
            Route::put('clients/{id}', [ClientController::class, 'update']);
            Route::patch('clients/{id}', [ClientController::class, 'update']);
            Route::delete('clients/{id}', [ClientController::class, 'destroy']);
            Route::post('clients/{clientId}/register', [ClientController::class, 'registerClientForExistingClient']);
            Route::post('clients/telephone', [ClientController::class, 'filterByTelephone']);

            Route::get('sendtestemail', [ClientController::class, 'sendTestEmail']);

            Route::post('dettes', [DetteController::class, 'store']);
            Route::get('dettes', [DetteController::class, 'index']);
            Route::get('/dettes/{id}', [DetteController::class, 'show']);
            Route::get('/dettes/{id}/articles', [DetteController::class, 'listArticles']);
            Route::get('/dettes/{id}/paiements', [DetteController::class, 'listPaiements']);
            Route::post('/dettes/{id}/paiements', [DetteController::class, 'addPaiement']);

            Route::get('notification/client/{id}', [NotificationController::class, 'sendToOneClient']);
            Route::post('notification/client/all', [NotificationController::class, 'sendToSpecificClients']);
            Route::post('notification/client/message', [NotificationController::class, 'sendCustomMessageToClients']);



        });

        // Routes accessibles uniquement par les Admins
        Route::middleware('can:isBoutiquier,isClient')->group(function (){
            Route::get('clients/{id}', [ClientController::class, 'show']);
            Route::post('clients/{id}/user', [ClientController::class, 'showClientWithUser']);

            Route::post('clients/{id}/dettes', [DetteController::class, 'getClientDettes']);
        });

        Route::middleware('can:isClient')->group(function (){
            Route::get('notifications/unread', [NotificationController::class, 'getUnreadNotifications']);
            Route::get('notifications/read', [NotificationController::class, 'getReadNotifications']);
        });

    });
});



