<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;


Route::post('/usuarios/login', [AuthController::class, 'login']);
Route::post('/usuarios', [UserController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    Route::post('/usuarios/logout', [AuthController::class, 'logout']);
    
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::patch('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
});