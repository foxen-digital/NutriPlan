<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CollectionRecipeController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RecipeImportController;
use App\Http\Controllers\UserRecipeController;
use App\Http\Controllers\MealPlanRecipeController;
use App\Http\Controllers\MealAssignmentController;
use App\Http\Controllers\MealPlanCopyController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ShoppingListItemController;
use App\Http\Controllers\ShoppingListItemPurchaseController;
use App\Http\Controllers\ShoppingListGenerationController;
use App\Http\Controllers\ShoppingListItemOrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('recipes', RecipeController::class);
    Route::post('recipes/import', RecipeImportController::class)->name('recipes.import');
    Route::get('recipes/by/{user}', [UserRecipeController::class, 'index'])->name('recipes.by-user');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
    Route::post('/categories', [CategoryController::class, 'store']);

    // Ingredients
    Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');

    Route::resource('collections', CollectionController::class);
    Route::post('collections/add-recipe', [CollectionRecipeController::class, 'store'])->name('collections.add-recipe');
    Route::delete('collections/{collection}/recipes/{recipe}', [CollectionRecipeController::class, 'destroy'])->name('collections.remove-recipe');

    Route::post('recipes/{recipe}/favorite', FavoriteController::class)->name('recipes.favorite');
    Route::get('favorites', [FavoritesController::class, 'index'])->name('favorites.index');

    Route::resource('meal-plans', MealPlanController::class)->except(['edit', 'update'])->parameters([
        'meal-plans' => 'mealPlan'
    ]);
    Route::post('meal-plans/add-recipe', [MealPlanRecipeController::class, 'store'])->name('meal-plans.add-recipe');
    Route::post('meal-plans/{mealPlan}/copy', MealPlanCopyController::class)->name('meal-plans.copy');
    Route::post('meal-plans/{mealPlan}/shopping-list', [ShoppingListGenerationController::class, 'store'])->name('meal-plans.generate-shopping-list');

    // Fix the parameter names to match the controller expectations
    Route::delete('meal-plans/{id}/recipes/{recipeId}', [MealPlanRecipeController::class, 'destroy'])
         ->name('meal-plans.remove-recipe');

    // Shopping list routes
    Route::resource('shopping-lists', ShoppingListController::class);
    Route::post('shopping-lists/{shoppingList}/items', [ShoppingListItemController::class, 'store'])->name('shopping-lists.items.store');
    Route::put('shopping-lists/{shoppingList}/items/{item}', [ShoppingListItemController::class, 'update'])->name('shopping-lists.items.update');
    Route::delete('shopping-lists/{shoppingList}/items/{item}', [ShoppingListItemController::class, 'destroy'])->name('shopping-lists.items.destroy');
    Route::post('shopping-lists/{shoppingList}/items/{item}/toggle-purchased', [ShoppingListItemPurchaseController::class, 'store'])->name('shopping-lists.items.toggle-purchased');
    Route::put('shopping-lists/{shoppingList}/order-items', ShoppingListItemOrderController::class)->name('shopping-lists.items.order');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/meal-assignments', [MealAssignmentController::class, 'store'])->name('meal-assignments.store');
    Route::put('/meal-assignments/{mealAssignment}', [MealAssignmentController::class, 'update'])->name('meal-assignments.update');
    Route::delete('/meal-assignments/{mealAssignment}', [MealAssignmentController::class, 'destroy'])->name('meal-assignments.destroy');
    Route::post('/meal-assignments/{mealAssignment}/toggle-cook', [MealAssignmentController::class, 'toggleToCook'])->name('meal-assignments.toggle-cook');
});

// Demo Routes
Route::get('/demo/toasts', function () {
    return Inertia::render('Demo/Toasts');
})->middleware(['auth'])->name('demo.toasts');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
