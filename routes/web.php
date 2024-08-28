<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\GameController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/player/create', [PlayerController::class, 'store']);
Route::put('/player/update/{id}', [PlayerController::class, 'update']);
Route::get('/player/list', [PlayerController::class, 'index']);
Route::get('/player/find/{id}', [PlayerController::class, 'show']);
Route::delete('/player/delete/{id}', [PlayerController::class, 'destroy']);

Route::post('/game/create', [GameController::class, 'store']);
Route::put('/game/update/{id}', [GameController::class, 'update']);
Route::get('/game/list', [GameController::class, 'index']);
Route::get('/game/find/{id}', [GameController::class, 'show']);
Route::delete('/game/delete/{id}', [GameController::class, 'destroy']);
Route::post('/game/confirm', [GameController::class, 'confirm']);
Route::get('/game/list-confirmed/{id}', [GameController::class, 'listConfirmed']);
Route::get('/game/create-teams/{id}', [GameController::class, 'createTeams']);