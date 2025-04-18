<?php

declare(strict_types=1);

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

// Apply RefreshDatabase trait globally for this file
uses(RefreshDatabase::class);

test('category resource transforms correctly', function () {
    $category = Category::factory()->create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $resource = new CategoryResource($category);
    $request = Request::create('/api/categories', 'GET');
    $transformed = $resource->toArray($request);

    $expected = [
        'id' => $category->id,
        'name' => 'Test Category',
    ];

    expect($transformed)->toEqual($expected);
});

test('category resource structure is correct', function () {
    $category = Category::factory()->make(); // No need to save
    $resource = new CategoryResource($category);
    $request = Request::create('/api/categories', 'GET');
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toHaveKeys(['id', 'name'])
        ->toHaveCount(2);
});
