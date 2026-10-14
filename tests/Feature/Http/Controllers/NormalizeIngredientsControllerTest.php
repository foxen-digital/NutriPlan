<?php

declare(strict_types=1);

use App\Ai\Agents\IngredientNormalizationAgent;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('returns 401 when unauthenticated', function () {
    postJson(route('ingredients.normalize'), [
        'ingredients' => ['1 cup rice'],
    ])->assertUnauthorized();
});

test('rejects invalid ingredient payloads', function (array $payload, string $key) {
    $response = actingAs($this->user)
        ->postJson(route('ingredients.normalize'), $payload);

    $response->assertJsonValidationErrors($key);

    expect(Ingredient::count())->toBe(0);
})->with([
    'missing ingredients' => [[], 'ingredients'],
    'empty array' => [['ingredients' => []], 'ingredients'],
    'empty string item' => [['ingredients' => ['']], 'ingredients.0'],
    'non-string item' => [['ingredients' => [2]], 'ingredients.0'],
    'overly long item' => [['ingredients' => [str_repeat('a', 256)]], 'ingredients.0'],
    'too many items' => [['ingredients' => array_fill(0, 51, '1 cup rice')], 'ingredients'],
]);

test('normalizes pasted lines into ingredient rows', function () {
    IngredientNormalizationAgent::fake([
        [
            'ingredients' => [
                [
                    'base_name' => 'plain flour',
                    'quantity' => 200,
                    'unit' => 'g',
                    'preparation_notes' => null,
                    'description' => 'plain flour',
                    'original_string' => '200 g plain flour',
                ],
                [
                    'base_name' => 'red onion',
                    'quantity' => 2,
                    'unit' => 'piece',
                    'preparation_notes' => 'finely chopped',
                    'description' => 'red onion, finely chopped',
                    'original_string' => '2 red onions, finely chopped',
                ],
            ],
        ],
    ]);

    $response = actingAs($this->user)
        ->postJson(route('ingredients.normalize'), [
            'ingredients' => [' 200 g plain flour ', '2 red onions, finely chopped'],
        ]);

    $response->assertOk();
    $response->assertJsonCount(2, 'ingredients');

    $flour = Ingredient::query()->where('name', 'plain flour')->first();
    $onion = Ingredient::query()->where('name', 'red onion')->first();

    expect($flour)->not->toBeNull();
    expect($onion)->not->toBeNull();

    expect($response->json('ingredients.0'))->toMatchArray([
        'ingredient_id' => $flour->id,
        'name' => 'plain flour',
        'amount' => 200,
        'unit' => 'g',
        'description' => 'plain flour',
        'original_string' => '200 g plain flour',
    ]);

    expect($response->json('ingredients.1'))->toMatchArray([
        'ingredient_id' => $onion->id,
        'name' => 'red onion',
        'amount' => 2,
        'unit' => 'piece',
        'description' => 'red onion, finely chopped',
        'original_string' => '2 red onions, finely chopped',
    ]);
});

test('reuses an existing ingredient instead of creating duplicates', function () {
    $existing = Ingredient::factory()->create(['name' => 'olive oil']);

    IngredientNormalizationAgent::fake([
        [
            'ingredients' => [
                [
                    'base_name' => 'olive oil',
                    'quantity' => 1,
                    'unit' => 'tbsp',
                    'preparation_notes' => null,
                    'description' => 'olive oil',
                    'original_string' => '1 tbsp olive oil',
                ],
                [
                    'base_name' => 'olive oil',
                    'quantity' => 2,
                    'unit' => 'tbsp',
                    'preparation_notes' => null,
                    'description' => 'olive oil',
                    'original_string' => '2 tbsp olive oil',
                ],
            ],
        ],
    ]);

    $response = actingAs($this->user)
        ->postJson(route('ingredients.normalize'), [
            'ingredients' => ['1 tbsp olive oil', '2 tbsp olive oil'],
        ]);

    $response->assertOk();

    expect(Ingredient::query()->where('name', 'olive oil')->count())->toBe(1);
    expect($response->json('ingredients.0.ingredient_id'))->toBe($existing->id);
    expect($response->json('ingredients.1.ingredient_id'))->toBe($existing->id);
});

test('falls back to the regex parser when the agent request fails', function () {
    IngredientNormalizationAgent::fake(function () {
        throw new \Exception('API request failed');
    });

    $response = actingAs($this->user)
        ->postJson(route('ingredients.normalize'), [
            'ingredients' => ['2 cups flour'],
        ]);

    $response->assertOk();
    $response->assertJsonCount(1, 'ingredients');

    $flour = Ingredient::query()->where('name', 'flour')->first();

    expect($response->json('ingredients.0'))->toMatchArray([
        'name' => 'flour',
        'amount' => 2.0,
        'unit' => 'cup',
    ]);
    $this->assertModelExists($flour);
});
