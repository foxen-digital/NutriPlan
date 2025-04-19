<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Laravel\Sanctum\PersonalAccessToken;

test('api tokens page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('settings.tokens.index'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('settings/ApiTokens')
        ->has('tokens')
    );
});

test('tokens can be created', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('settings.tokens.store'), [
            'name' => 'Test Token',
        ]);

    $response->assertSessionHas('token');
    $response->assertSessionHas('status', 'token-created');
    $response->assertRedirect(route('settings.tokens.index'));

    $this->assertDatabaseHas('personal_access_tokens', [
        'name' => 'Test Token',
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
    ]);
});

test('token name is required', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('settings.tokens.store'), [
            'name' => '',
        ]);

    $response->assertSessionHasErrors('name');
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('tokens can be deleted', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Token');
    $tokenId = PersonalAccessToken::findToken($token->plainTextToken)->id;

    $response = $this
        ->actingAs($user)
        ->delete(route('settings.tokens.destroy', $tokenId));

    $response->assertSessionHas('status', 'token-deleted');
    $response->assertRedirect(route('settings.tokens.index'));

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $tokenId,
    ]);
});

test('unauthenticated users cannot view tokens', function () {
    $response = $this->get(route('settings.tokens.index'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated users cannot create tokens', function () {
    $response = $this->post(route('settings.tokens.store'), [
        'name' => 'Test Token',
    ]);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('unauthenticated users cannot delete tokens', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Token');
    $tokenId = PersonalAccessToken::findToken($token->plainTextToken)->id;

    // Attempt to delete without authentication
    $response = $this->delete(route('settings.tokens.destroy', $tokenId));

    $response->assertRedirect(route('login'));
    $this->assertDatabaseCount('personal_access_tokens', 1);
});

test('users cannot delete others tokens', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // User A creates a token
    $token = $userA->createToken('User A Token');
    $tokenId = PersonalAccessToken::findToken($token->plainTextToken)->id;

    // User B tries to delete User A's token
    $response = $this
        ->actingAs($userB)
        ->delete(route('settings.tokens.destroy', $tokenId));

    $response->assertRedirect(route('settings.tokens.index'));

    // Token should still exist
    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $tokenId,
    ]);
});
