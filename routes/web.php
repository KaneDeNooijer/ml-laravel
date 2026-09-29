<?php

use App\Http\Controllers\DemoController;
use App\Http\Controllers\IngredientCategoryController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', DemoController::class)->name('demo');
Route::resource('meals', MealController::class)->except('show');
Route::get('/api/ingredient-categories', IngredientCategoryController::class)->name('ingredient-categories.index');
Route::post('/api/scans', [ScanController::class, 'store'])->name('scans.store');
Route::post('/api/scans/{scan}/accept', [ScanController::class, 'accept'])->name('scans.accept');
