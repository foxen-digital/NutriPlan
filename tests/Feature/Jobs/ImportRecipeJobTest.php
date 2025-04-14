<?php

declare(strict_types=1);

use App\Actions\FetchRecipe;
use App\Events\RecipeImportCompleted;
use App\Exceptions\RecipeImport\ConnectionFailedException;
use App\Exceptions\RecipeImport\NoStructuredDataException;
use App\Jobs\ImportRecipeJob;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

beforeEach(function () {
    Queue::fake();
    Event::fake();
    $this->user = User::factory()->create();
    $this->url = 'https://example.com/recipe';
});

it('imports a recipe for a user and dispatches a success event', function () {
    // Arrange
    $recipe = Recipe::factory()->create([
        'title' => 'Test Recipe',
        'url' => $this->url,
        'slug' => Str::slug('Test Recipe'),
    ]);

    $fetchRecipeMock = $this->mock(FetchRecipe::class);
    $fetchRecipeMock->shouldReceive('handle')
        ->once()
        ->with($this->url)
        ->andReturn($recipe);

    // Act
    $job = new ImportRecipeJob($this->url, $this->user->id);
    $result = $job->handle($fetchRecipeMock);

    // Assert
    expect($result)->toBeInstanceOf(Recipe::class)
        ->and($result->id)->toBe($recipe->id)
        ->and($result->title)->toBe('Test Recipe')
        ->and($result->url)->toBe($this->url);

    // Assert that the event was dispatched with the correct parameters
    Event::assertDispatched(RecipeImportCompleted::class, function ($event) use ($recipe) {
        return $event->userId === $this->user->id &&
               $event->status === 'success' &&
               $event->recipe === $recipe;
    });
});

it('handles recipe parsing failures and dispatches an error event', function () {
    // Arrange
    $fetchRecipeMock = $this->mock(FetchRecipe::class);
    $fetchRecipeMock->shouldReceive('handle')
        ->once()
        ->with($this->url)
        ->andThrow(new NoStructuredDataException($this->url));

    // Act & Assert
    $job = new ImportRecipeJob($this->url, $this->user->id);

    expect(fn () => $job->handle($fetchRecipeMock))
        ->toThrow(NoStructuredDataException::class);

    // Assert that the event was dispatched with the correct parameters
    Event::assertDispatched(RecipeImportCompleted::class, function ($event) {
        return $event->userId === $this->user->id &&
               $event->status === 'error' &&
               $event->recipe === null;
    });
});

it('handles connection failures and dispatches an error event', function () {
    // Arrange
    $fetchRecipeMock = $this->mock(FetchRecipe::class);
    $fetchRecipeMock->shouldReceive('handle')
        ->once()
        ->with($this->url)
        ->andThrow(new ConnectionFailedException($this->url, 'Connection timed out'));

    // Act & Assert
    $job = new ImportRecipeJob($this->url, $this->user->id);

    expect(fn () => $job->handle($fetchRecipeMock))
        ->toThrow(ConnectionFailedException::class);

    // Assert that the event was dispatched with the correct parameters
    Event::assertDispatched(RecipeImportCompleted::class, function ($event) {
        return $event->userId === $this->user->id &&
               $event->status === 'error' &&
               $event->recipe === null;
    });
});
