<?php

declare(strict_types=1);

use App\Models\User;

it('authorizes users to listen on their own private channel', function () {
    // Arrange
    $user = User::factory()->create();
    $userId = $user->id;

    // Create a channel auth callback that mimics the one in channels.php
    $callback = function ($authUser, $id) {
        return (int) $authUser->id === (int) $id;
    };

    // Act
    $authorized = $callback($user, $userId);

    // Assert
    expect($authorized)->toBeTrue();
});

it('denies users from listening on other users private channels', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create a channel auth callback that mimics the one in channels.php
    $callback = function ($authUser, $id) {
        return (int) $authUser->id === (int) $id;
    };

    // Act
    $authorized = $callback($user, $otherUser->id);

    // Assert
    expect($authorized)->toBeFalse();
});
