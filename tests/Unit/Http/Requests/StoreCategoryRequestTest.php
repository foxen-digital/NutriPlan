<?php

declare(strict_types=1);

use App\Http\Requests\StoreCategoryRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

// No need for TestCase use statement with Pest

beforeEach(function () {
    // Use Pest's shared context ($this)
    $this->request = new StoreCategoryRequest();
    $this->rules = $this->request->rules();
});

test('authorize returns true', function () {
    expect($this->request->authorize())->toBeTrue();
});

dataset('validation data', function () {
    return [
        'name: required' => ['name', '', false],
        'name: not string' => ['name', 123, false],
        'name: too long' => ['name', str_repeat('a', 256), false],
        'name: valid' => ['name', 'Valid Category Name', true],
        // Note: 'unique' rule requires database interaction, best tested in feature tests.
    ];
});

test('validation rules', function (string $field, mixed $value, bool $shouldPass, array $data = []) {
    $validator = Validator::make(
        array_merge([$field => $value], $data),
        [$field => $this->rules[$field]]
    );

    expect($validator->passes())->toBe($shouldPass, $validator->errors()->toJson());
})->with('validation data');

test('rules structure matches expected', function () {
    $expectedRules = [
        'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')],
    ];

    // Compare serialized rules for reliable comparison of Rule objects
    expect(json_encode($this->rules))
        ->toBeJson()
        ->json()
        ->toEqual(json_decode(json_encode($expectedRules), true)); // Need to decode expected for comparison
});
