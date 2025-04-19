<?php

declare(strict_types=1);

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

// Apply RefreshDatabase trait globally for this file
uses(RefreshDatabase::class);

it('transforms correctly', function () {
    $category = Category::factory()->create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $resource = new CategoryResource($category);
    $request = Request::create(route('categories.index'), 'GET');
    $transformed = $resource->toArray($request);

    $expected = [
        'id' => $category->id,
        'name' => 'Test Category',
    ];

    expect($transformed)->toEqual($expected);
});

it('has correct structure', function () {
    $category = Category::factory()->make(); // No need to save
    $resource = new CategoryResource($category);
    $request = Request::create(route('categories.index'), 'GET');
    $transformed = $resource->toArray($request);

    expect($transformed)
        ->toHaveKeys(['id', 'name'])
        ->toHaveCount(2);
});
