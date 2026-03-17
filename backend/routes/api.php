<?php

use App\Models\Country;
use Illuminate\Support\Facades\Route;

Route::get('/countries', fn () => Country::select()->with('players')->get());
Route::get('/players', fn () => response()->json([]));
Route::get('/matches', fn () => response()->json([]));
