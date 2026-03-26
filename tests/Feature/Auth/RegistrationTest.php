<?php

declare(strict_types=1);

test('registration screen renders when registration is enabled', function () {
    config(['auth.registration_enabled' => true]);

    $this->get('/register')->assertSuccessful()->assertInertia(
        fn ($page) => $page->component('auth/Register')
    );
});

test('registration screen shows closed page when registration is disabled', function () {
    config(['auth.registration_enabled' => false]);

    $this->get('/register')->assertSuccessful()->assertInertia(
        fn ($page) => $page->component('auth/RegistrationClosed')
    );
});

test('new users can register when registration is enabled', function () {
    config(['auth.registration_enabled' => true]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('recipes.index', absolute: false));
});

test('new users cannot register when registration is disabled', function () {
    config(['auth.registration_enabled' => false]);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertForbidden();

    $this->assertGuest();
});
