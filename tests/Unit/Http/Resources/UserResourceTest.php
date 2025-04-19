<?php

declare(strict_types=1);

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can transform basic user data correctly', function () {
    $user = User::factory()->create();

    $resource = new UserResource($user);
    $array = $resource->toArray(request());

    expect($array)
        ->toBeArray()
        ->toHaveKeys(['id', 'name', 'slug'])
        ->and($array['id'])->toBe($user->id)
        ->and($array['name'])->toBe($user->name)
        ->and($array['slug'])->toBe($user->slug);
});

it('does not include sensitive data', function () {
    $user = User::factory()->create();

    $resource = new UserResource($user);
    $array = $resource->toArray(request());

    expect($array)
        ->not->toHaveKeys([
            'email',
            'email_verified_at',
            'password',
            'remember_token',
            'created_at',
            'updated_at'
        ]);
});

it('maintains slug from model', function () {
    $user = User::factory()->create();
    $originalSlug = $user->slug;

    $resource = new UserResource($user);
    $array = $resource->toArray(request());

    expect($array)
        ->toHaveKey('slug')
        ->and($array['slug'])->toBe($originalSlug);
});

it('transforms collection correctly', function () {
    $users = User::factory()->count(3)->create();

    $collection = UserResource::collection($users);
    $array = $collection->toArray(request());

    expect($array)->toBeArray()->toHaveCount(3);

    foreach ($array as $index => $item) {
        expect($item)
            ->toHaveKeys(['id', 'name', 'slug'])
            ->and($item['id'])->toBe($users[$index]->id)
            ->and($item['name'])->toBe($users[$index]->name)
            ->and($item['slug'])->toBe($users[$index]->slug);
    }
});
