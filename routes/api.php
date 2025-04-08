<?php

use App\Http\Controllers\GameController;
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

Route::apiResource('wheels', GameController::class);
Route::get('background', [GameController::class, 'getBackground']);
Route::post('background', [GameController::class, 'updateBackground']);
Route::get('history', [GameController::class, 'getHistory']);
Route::post('history', [GameController::class, 'storeHistory']);
Route::delete('history', [GameController::class, 'deleteHistory']);
Route::get('current-wheel', [GameController::class, 'getCurrentWheel']);
Route::post('current-wheel', [GameController::class, 'updateCurrentWheel']);
Route::get('wheels', [GameController::class, 'getWheels']);
