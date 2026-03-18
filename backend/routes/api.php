<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CountryRosterController;
use App\Http\Controllers\PlayerController;
use App\Models\Country;
use Illuminate\Support\Facades\Route;

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

// ─── Public ───────────────────────────────────────────────────────────────────
Route::get('/countries', fn () => response()->json(['data' => Country::query()->with('players')->orderBy('name')->get()]));
Route::get('/players', [PlayerController::class, 'index']);
Route::get('/roster/{countryId}', [CountryRosterController::class, 'index']);
Route::get('/rosters', [CountryRosterController::class, 'list']);

// ─── Protected ────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/matches', fn () => response()->json(['data' => []]));
    Route::post('/roster', [CountryRosterController::class, 'store']);
    Route::delete('/roster/{countryId}/player/{playerId}', [CountryRosterController::class, 'destroy']);
});
