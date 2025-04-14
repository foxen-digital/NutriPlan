<?php

declare(strict_types=1);

use App\Actions\FetchRecipe;
use App\Exceptions\RecipeImport\ConnectionFailedException;
use App\Exceptions\RecipeImport\NoStructuredDataException;
use App\Jobs\ImportRecipeJob;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->user = User::factory()->create();
    $this->url = 'https://example.com/recipe';
});

it('imports a recipe for a user', function () {
    // Arrange
    $recipe = Recipe::factory()->create([
        'title' => 'Test Recipe',
        'url' => $this->url,
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
});

it('handles recipe parsing failures', function () {
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
});

it('handles connection failures', function () {
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
});
