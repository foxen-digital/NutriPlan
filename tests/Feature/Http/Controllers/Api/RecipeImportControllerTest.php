<?php

declare(strict_types=1);

use App\Jobs\ImportRecipeJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

test('valid url can be submitted for import via api', function () {
    // Arrange
    $user = User::factory()->create();
    $url = 'https://example.com/recipe/chocolate-cake';

    // Authenticate the user with Sanctum
    Sanctum::actingAs($user, ['*']);

    // Act
    $response = $this->postJson(route('api.recipes.import-via-extension'), [
        'url' => $url,
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_ACCEPTED)
        ->assertJson([
            'message' => 'Recipe import queued successfully.',
        ]);

    // Assert the job was dispatched with the correct parameters
    Queue::assertPushed(ImportRecipeJob::class, function ($job) use ($url, $user) {
        return $job->url === $url && $job->userId === $user->id;
    });
});

test('unauthenticated request is rejected', function () {
    // Arrange
    $url = 'https://example.com/recipe/chocolate-cake';

    // Act
    $response = $this->postJson(route('api.recipes.import-via-extension'), [
        'url' => $url,
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    // Assert no job was dispatched
    Queue::assertNothingPushed();
});

test('invalid url validation fails', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Act
    $response = $this->postJson(route('api.recipes.import-via-extension'), [
        'url' => 'not-a-valid-url',
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['url']);

    // Assert no job was dispatched
    Queue::assertNothingPushed();
});

test('missing url validation fails', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Act
    $response = $this->postJson(route('api.recipes.import-via-extension'), []);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['url']);

    // Assert no job was dispatched
    Queue::assertNothingPushed();
});

test('invalid protocol validation fails', function () {
    // Arrange
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Act
    $response = $this->postJson(route('api.recipes.import-via-extension'), [
        'url' => 'ftp://example.com/recipe',
    ]);

    // Assert
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors(['url']);

    // Assert no job was dispatched
    Queue::assertNothingPushed();
});
