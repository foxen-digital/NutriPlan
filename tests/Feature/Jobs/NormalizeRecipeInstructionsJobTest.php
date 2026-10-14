<?php

declare(strict_types=1);

use App\Events\RecipeInstructionsNormalized;
use App\Jobs\NormalizeRecipeInstructionsJob;
use App\Models\Recipe;
use App\Models\User;
use App\Services\InstructionNormalizationService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

beforeEach(function () {
    Queue::fake();
    Event::fake([RecipeInstructionsNormalized::class]);
    $this->user = User::factory()->create();
});

it('normalizes instructions and dispatches a success event', function () {
    // Arrange
    $recipe = Recipe::factory()->for($this->user)->create([
        'title' => 'Test Recipe',
        'instructions' => "step 1\ndo the thing",
        'slug' => Str::slug('Test Recipe'),
    ]);

    $serviceMock = $this->mock(InstructionNormalizationService::class);
    $serviceMock->shouldReceive('normalize')
        ->once()
        ->with("step 1\ndo the thing")
        ->andReturn("1. **Step 1**\n2. **Do the thing**");

    // Act
    $job = new NormalizeRecipeInstructionsJob($recipe->id, $this->user->id);
    $job->handle($serviceMock);

    // Assert
    expect($recipe->refresh()->instructions)->toBe("1. **Step 1**\n2. **Do the thing**");

    Event::assertDispatched(RecipeInstructionsNormalized::class, function ($event) use ($recipe) {
        return $event->userId === $this->user->id &&
               $event->status === 'success' &&
               $event->recipe !== null &&
               $event->recipe->id === $recipe->id;
    });
});

it('dispatches a warning event and keeps raw instructions when the service falls back', function () {
    // Arrange
    $recipe = Recipe::factory()->for($this->user)->create([
        'title' => 'Test Recipe',
        'instructions' => 'raw instructions',
        'slug' => Str::slug('Test Recipe'),
    ]);

    $serviceMock = $this->mock(InstructionNormalizationService::class);
    $serviceMock->shouldReceive('normalize')
        ->once()
        ->with('raw instructions')
        ->andReturn('raw instructions');

    // Act
    $job = new NormalizeRecipeInstructionsJob($recipe->id, $this->user->id);
    $job->handle($serviceMock);

    // Assert
    expect($recipe->refresh()->instructions)->toBe('raw instructions');

    Event::assertDispatched(RecipeInstructionsNormalized::class, function ($event) use ($recipe) {
        return $event->userId === $this->user->id &&
               $event->status === 'warning' &&
               $event->recipe !== null &&
               $event->recipe->id === $recipe->id;
    });
});

it('skips normalization without an event for degenerate input', function () {
    // Arrange
    $recipe = Recipe::factory()->for($this->user)->create([
        'title' => 'Test Recipe',
        'instructions' => '0',
        'slug' => Str::slug('Test Recipe'),
    ]);

    $serviceMock = $this->mock(InstructionNormalizationService::class);
    $serviceMock->shouldNotReceive('normalize');

    // Act
    $job = new NormalizeRecipeInstructionsJob($recipe->id, $this->user->id);
    $job->handle($serviceMock);

    // Assert
    expect($recipe->refresh()->instructions)->toBe('0');

    Event::assertNothingDispatched();
});

it('skips normalization without an event when the recipe no longer exists', function () {
    // Arrange
    $serviceMock = $this->mock(InstructionNormalizationService::class);
    $serviceMock->shouldNotReceive('normalize');

    // Act
    $job = new NormalizeRecipeInstructionsJob(999999, $this->user->id);
    $job->handle($serviceMock);

    // Assert
    Event::assertNothingDispatched();
    expect(auth()->id())->toBeNull();
});

it('dispatches an error event and rethrows on failure', function () {
    // Arrange
    $recipe = Recipe::factory()->for($this->user)->create([
        'title' => 'Test Recipe',
        'instructions' => 'raw instructions',
        'slug' => Str::slug('Test Recipe'),
    ]);

    $serviceMock = $this->mock(InstructionNormalizationService::class);
    $serviceMock->shouldReceive('normalize')
        ->once()
        ->andThrow(new RuntimeException('Database unavailable'));

    // Act & Assert
    $job = new NormalizeRecipeInstructionsJob($recipe->id, $this->user->id);

    expect(fn () => $job->handle($serviceMock))
        ->toThrow(RuntimeException::class);

    Event::assertDispatched(RecipeInstructionsNormalized::class, function ($event) {
        return $event->userId === $this->user->id &&
               $event->status === 'error' &&
               $event->recipe === null;
    });

    expect(auth()->id())->toBeNull();
});
