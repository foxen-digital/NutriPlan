<?php

declare(strict_types=1);

use App\Models\Recipe;
use App\Events\RecipeImportCompleted;

beforeEach(function () {
    $this->recipe = Recipe::factory()->create([
        'title' => 'Test Recipe',
        'url' => 'https://example.com/recipe',
        'slug' => Str::slug('Test Recipe'),
    ]);
});

it('has the correct data to broadcast', function () {
    // Arrange
    $userId = 1;
    $status = 'success';
    $message = 'Recipe imported successfully!';

    // Act
    $event = new RecipeImportCompleted($userId, $status, $message, $this->recipe);

    // Assert
    // Test properties
    expect($event->userId)->toBe($userId);
    expect($event->status)->toBe($status);
    expect($event->message)->toBe($message);
    expect($event->recipe)->toBe($this->recipe);

    // Test broadcast data
    expect($event->broadcastWith())->toBe([
        'status' => $status,
        'message' => $message,
        'recipeId' => $this->recipe->id,
        'recipeUrl' => route('recipes.show', $this->recipe->slug),
    ]);
});

it('broadcasts on the correct channel', function () {
    // Arrange
    $userId = 1;
    $event = new RecipeImportCompleted($userId, 'success', 'Recipe imported!', $this->recipe);

    // Act
    $channels = $event->broadcastOn();

    // Assert
    expect($channels)->toHaveCount(1);
    expect($channels[0]->name)->toBe("private-user.{$userId}");
});

it('excludes recipe data when recipe is null', function () {
    // Arrange
    $event = new RecipeImportCompleted(1, 'error', 'No recipe data found.', null);

    // Act & Assert
    expect($event->broadcastWith())->toBe([
        'status' => 'error',
        'message' => 'No recipe data found.',
    ]);
});

it('has the correct broadcast name', function () {
    // Arrange
    $event = new RecipeImportCompleted(1, 'success', 'Recipe imported!', $this->recipe);

    // Act & Assert
    expect($event->broadcastAs())->toBe('recipe.import.completed');
});
