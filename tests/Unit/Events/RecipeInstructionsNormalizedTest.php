<?php

declare(strict_types=1);

use App\Events\RecipeInstructionsNormalized;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->recipe = Recipe::factory()->for($this->user)->create([
        'title' => 'Test Recipe',
        'slug' => Str::slug('Test Recipe'),
    ]);
});

it('has the correct data to broadcast', function () {
    // Arrange
    $status = 'success';
    $message = "Instructions for 'Test Recipe' have been formatted.";

    // Act
    $event = new RecipeInstructionsNormalized($this->user->id, $status, $message, $this->recipe);

    // Assert
    // Test properties
    expect($event->userId)->toBe($this->user->id);
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
    $event = new RecipeInstructionsNormalized($this->user->id, 'success', 'Instructions formatted!', $this->recipe);

    // Act
    $channels = $event->broadcastOn();

    // Assert
    expect($channels)->toHaveCount(1);
    expect($channels[0]->name)->toBe("private-user.{$this->user->id}");
});

it('excludes recipe data when recipe is null', function () {
    // Arrange
    $event = new RecipeInstructionsNormalized(1, 'error', 'We could not format the instructions.', null);

    // Act & Assert
    expect($event->broadcastWith())->toBe([
        'status' => 'error',
        'message' => 'We could not format the instructions.',
    ]);
});

it('has the correct broadcast name', function () {
    // Arrange
    $event = new RecipeInstructionsNormalized(1, 'success', 'Instructions formatted!', $this->recipe);

    // Act & Assert
    expect($event->broadcastAs())->toBe('recipe.instructions.normalized');
});
