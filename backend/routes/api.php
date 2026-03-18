<?php

use App\Http\Controllers\CountryRosterController;
use App\Http\Controllers\PlayerController;
use App\Models\Country;
use Illuminate\Support\Facades\Route;

Route::get('/countries', fn () => response()->json(['data' => Country::query()->with('players')->orderBy('name')->get()]));
Route::get('/players', [PlayerController::class, 'index']);
Route::get('/matches', fn () => response()->json(['data' => []]));

Route::get('/roster/{countryId}', [CountryRosterController::class, 'index']);
Route::get('/rosters', [CountryRosterController::class, 'list']);
Route::post('/roster', [CountryRosterController::class, 'store']);
Route::delete('/roster/{countryId}/player/{playerId}', [CountryRosterController::class, 'destroy']);
