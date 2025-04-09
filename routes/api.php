<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;

Route::middleware('throttle:100,1')->apiResource('users', UserController::class)
    ->only([
        'index', 'show', 'store', 'update', 'destroy'
    ]);