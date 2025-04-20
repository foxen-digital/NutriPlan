<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreCollectionRecipeRequest;
use App\Models\Collection;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Mockery;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->request = new StoreCollectionRecipeRequest();
});

dataset('validation_cases', [
    'pass_case' => [
        'data' => ['collection_id' => 'valid_collection_id', 'recipe_id' => 'valid_recipe_id'],
        'shouldPass' => true,
    ],
    'fail_missing_collection_id' => [
        'data' => ['recipe_id' => 'valid_recipe_id'],
        'shouldPass' => false,
        'expectedErrors' => ['collection_id'],
    ],
    'fail_missing_recipe_id' => [
        'data' => ['collection_id' => 'valid_collection_id'],
        'shouldPass' => false,
        'expectedErrors' => ['recipe_id'],
    ],
    'fail_non_existent_collection_id' => [
        'data' => ['collection_id' => 999, 'recipe_id' => 'valid_recipe_id'],
        'shouldPass' => false,
        'expectedErrors' => ['collection_id'],
    ],
    'fail_non_existent_recipe_id' => [
        'data' => ['collection_id' => 'valid_collection_id', 'recipe_id' => 999],
        'shouldPass' => false,
        'expectedErrors' => ['recipe_id'],
    ],
    'fail_both_missing' => [
        'data' => [],
        'shouldPass' => false,
        'expectedErrors' => ['collection_id', 'recipe_id'],
    ],
]);

test('validation rules', function (array $data, bool $shouldPass, array $expectedErrors = []) {
    // Create models needed for 'exists' rules
    $collection = Collection::factory()->create();
    $recipe = Recipe::factory()->create();

    // Replace placeholder IDs with actual created IDs if needed
    if (isset($data['collection_id']) && $data['collection_id'] === 'valid_collection_id') {
        $data['collection_id'] = $collection->id;
    }
    if (isset($data['recipe_id']) && $data['recipe_id'] === 'valid_recipe_id') {
        $data['recipe_id'] = $recipe->id;
    }

    $validator = Validator::make($data, $this->request->rules());

    expect(!$validator->fails())->toBe($shouldPass);

    if (!$shouldPass) {
        expect($validator->errors()->keys())->toBe($expectedErrors);
    }
})->with('validation_cases');

test('authorize returns true when user can update collection', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->create(['user_id' => $user->id]);

    // Use Mockery::on to match any Collection instance with the correct ID
    Gate::shouldReceive('allows')
        ->once()
        ->with('update', Mockery::on(function ($arg) use ($collection) {
            return $arg instanceof Collection && $arg->id === $collection->id;
        }))
        ->andReturn(true);

    // Set the user making the request
    actingAs($user);

    // Set the input data for the request
    $this->request->merge(['collection_id' => $collection->id]);

    expect($this->request->authorize())->toBeTrue();
});

test('authorize returns false when collection does not exist', function () {
    $user = User::factory()->create();

    // Mock Gate facade - it should not be called
    Gate::shouldReceive('allows')->never();

    // Set the user making the request
    actingAs($user);

    // Set non-existent collection_id
    $this->request->merge(['collection_id' => 999]);

    expect($this->request->authorize())->toBeFalse();
});

test('authorize returns false when user cannot update collection', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $collection = Collection::factory()->create(['user_id' => $otherUser->id]);

    // Use Mockery::on to match any Collection instance with the correct ID
    Gate::shouldReceive('allows')
        ->once()
        ->with('update', Mockery::on(function ($arg) use ($collection) {
            return $arg instanceof Collection && $arg->id === $collection->id;
        }))
        ->andReturn(false);

    // Set the user making the request
    actingAs($user);

    // Set the input data for the request
    $this->request->merge(['collection_id' => $collection->id]);

    expect($this->request->authorize())->toBeFalse();
});
