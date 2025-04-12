<?php

use App\Http\Controllers\Api\RecipeSearchController;
use App\Http\Controllers\Api\BarcodeLookupController;
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

Route::middleware('auth')->get('/user', function (Request $request) {
    return $request->user();
});

// Use web middleware to ensure it works with session authentication
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('recipes/search', RecipeSearchController::class)->name('api.recipes.search');
    Route::post('barcode-lookup', [BarcodeLookupController::class, 'lookup'])->name('api.barcode-lookup');
});
