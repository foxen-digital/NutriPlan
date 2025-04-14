<?php

declare(strict_types=1);

use App\Jobs\ImportRecipeJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('dispatches an import job and returns a redirect response', function () {
    // Act
    $response = $this->post(route('recipes.import'), [
        'url' => 'https://example.com/recipe',
    ]);

    // Assert
    $response->assertRedirect()
        ->assertSessionHas('success', 'Recipe import started. You will be notified when it completes.');

    Queue::assertPushed(ImportRecipeJob::class, function (ImportRecipeJob $job) {
        return $job->url === 'https://example.com/recipe' &&
               $job->userId === $this->user->id;
    });
});

it('validates the URL', function () {
    // Act
    $response = $this->post(route('recipes.import'), [
        'url' => 'not-a-valid-url',
    ]);

    // Assert
    $response->assertSessionHasErrors(['url']);

    Queue::assertNotPushed(ImportRecipeJob::class);
});

it('requires authentication', function () {
    // Arrange
    auth()->logout();

    // Act
    $response = $this->post(route('recipes.import'), [
        'url' => 'https://example.com/recipe',
    ]);

    // Assert
    $response->assertRedirect(route('login'));
    Queue::assertNotPushed(ImportRecipeJob::class);
});
