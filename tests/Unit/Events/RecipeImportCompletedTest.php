<?php

declare(strict_types=1);

use App\Events\RecipeImportCompleted;

it('has the correct data to broadcast', function () {
    // Arrange
    $userId = 1;
    $status = 'success';
    $message = 'Recipe imported successfully!';
    $recipeId = 123;

    // Act
    $event = new RecipeImportCompleted($userId, $status, $message, $recipeId);

    // Assert
    // Test properties
    expect($event->userId)->toBe($userId);
    expect($event->status)->toBe($status);
    expect($event->message)->toBe($message);
    expect($event->recipeId)->toBe($recipeId);

    // Test broadcast data
    expect($event->broadcastWith())->toBe([
        'status' => $status,
        'message' => $message,
        'recipeId' => $recipeId,
    ]);
});

it('broadcasts on the correct channel', function () {
    // Arrange
    $userId = 1;
    $event = new RecipeImportCompleted($userId, 'success', 'Recipe imported!', 123);

    // Act
    $channels = $event->broadcastOn();

    // Assert
    expect($channels)->toHaveCount(1);
    expect($channels[0]->name)->toBe("private-user.{$userId}");
});

it('has the correct broadcast name', function () {
    // Arrange
    $event = new RecipeImportCompleted(1, 'success', 'Recipe imported!', 123);

    // Act & Assert
    expect($event->broadcastAs())->toBe('recipe.import.completed');
});
