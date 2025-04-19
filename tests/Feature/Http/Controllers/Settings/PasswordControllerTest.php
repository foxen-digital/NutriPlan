<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

test('user can view password settings page', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('password.edit'));

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
            ->component('settings/Password')
            ->has('mustVerifyEmail')
            ->has('status')
    );
});

test('user can update password with valid current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('password.edit'))
        ->put(route('password.update'), [
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('password.edit'));

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('user cannot update password with incorrect current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect();

    expect(Hash::check('current-password', $user->fresh()->password))->toBeTrue();
});

test('new password must be confirmed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('password.update'), [
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect();
});

test('new password must meet minimum requirements', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('password.update'), [
            'current_password' => 'current-password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect();
});

test('current password is required', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('password.update'), [
            'current_password' => '',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect();
});

test('new password is required', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('password.update'), [
            'current_password' => 'current-password',
            'password' => '',
            'password_confirmation' => '',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect();
});
