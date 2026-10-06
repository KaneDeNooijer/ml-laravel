<?php

declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\ScannerController;
use Illuminate\Support\Facades\Route;

Route::resource('/', HomeController::class)
    ->only(['index'])
    ->names('home');

Route::resource('account', AccountController::class)
    ->only(['index'])
    ->names('account');

Route::resource('scanner', ScannerController::class)
    ->only(['index'])
    ->names('scanner');

Route::resource('meals', MealController::class)
    ->names('meals');
