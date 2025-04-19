<?php

declare(strict_types=1);

use App\Models\Collection;
use App\Models\User;
use App\Policies\CollectionPolicy;

beforeEach(function () {
    $this->policy = new CollectionPolicy();
});

test('viewAny returns true for authenticated users', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('view returns true for collection owner', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($this->policy->view($user, $collection))->toBeTrue();
});

test('view returns false for non owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    expect($this->policy->view($user, $collection))->toBeFalse();
});

test('create returns true for authenticated users', function () {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeTrue();
});

test('update returns true for collection owner', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($this->policy->update($user, $collection))->toBeTrue();
});

test('update returns false for non owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    expect($this->policy->update($user, $collection))->toBeFalse();
});

test('delete returns true for collection owner', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($this->policy->delete($user, $collection))->toBeTrue();
});

test('delete returns false for non owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    expect($this->policy->delete($user, $collection))->toBeFalse();
});

test('restore returns true for collection owner', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($this->policy->restore($user, $collection))->toBeTrue();
});

test('restore returns false for non owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    expect($this->policy->restore($user, $collection))->toBeFalse();
});

test('forceDelete returns true for collection owner', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($this->policy->forceDelete($user, $collection))->toBeTrue();
});

test('forceDelete returns false for non owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    expect($this->policy->forceDelete($user, $collection))->toBeFalse();
});
