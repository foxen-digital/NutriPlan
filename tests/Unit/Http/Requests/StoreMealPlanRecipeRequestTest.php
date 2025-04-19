<?php

declare(strict_types=1);

use App\Http\Requests\StoreMealPlanRecipeRequest;
use App\Models\MealPlan;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->mealPlan = MealPlan::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->otherUserMealPlan = MealPlan::factory()->create();
    $this->request = new StoreMealPlanRecipeRequest();
    $this->recipe = Recipe::factory()->create();
});

test('validation passes with valid data', function () {
    $this->request->merge([
        'meal_plan_id' => $this->mealPlan->id,
        'recipe_id' => $this->recipe->id,
        'scale_factor' => 1.5,
    ]);

    $rules = $this->request->rules();
    $validator = Validator::make($this->request->all(), $rules);

    expect($validator->passes())->toBeTrue();
});

test('validation fails when meal_plan_id is missing', function () {
    $this->request->merge([
        'recipe_id' => $this->recipe->id,
        'scale_factor' => 1.5,
    ]);

    $rules = $this->request->rules();
    $validator = Validator::make($this->request->all(), $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->has('meal_plan_id'))->toBeTrue();
});

test('validation fails when recipe_id is missing', function () {
    $this->request->merge([
        'meal_plan_id' => $this->mealPlan->id,
        'scale_factor' => 1.5,
    ]);

    $rules = $this->request->rules();
    $validator = Validator::make($this->request->all(), $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->has('recipe_id'))->toBeTrue();
});

test('validation fails when scale_factor is less than 0.01', function () {
    $this->request->merge([
        'meal_plan_id' => $this->mealPlan->id,
        'recipe_id' => $this->recipe->id,
        'scale_factor' => 0.001,
    ]);

    $rules = $this->request->rules();
    $validator = Validator::make($this->request->all(), $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->has('scale_factor'))->toBeTrue();
});

test('validation fails when scale_factor is greater than 100', function () {
    $this->request->merge([
        'meal_plan_id' => $this->mealPlan->id,
        'recipe_id' => $this->recipe->id,
        'scale_factor' => 150,
    ]);

    $rules = $this->request->rules();
    $validator = Validator::make($this->request->all(), $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->has('scale_factor'))->toBeTrue();
});

test('authorize returns true when user owns meal plan', function () {
    $this->actingAs($this->user);

    Gate::shouldReceive('allows')
        ->once()
        ->with('update', Mockery::type(MealPlan::class))
        ->andReturn(true);

    $this->request->merge(['meal_plan_id' => $this->mealPlan->id]);

    expect($this->request->authorize())->toBeTrue();
});

test('authorize returns false when user does not own meal plan', function () {
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);

    Gate::shouldReceive('allows')
        ->once()
        ->with('update', Mockery::type(MealPlan::class))
        ->andReturn(false);

    $this->request->merge(['meal_plan_id' => $this->mealPlan->id]);

    expect($this->request->authorize())->toBeFalse();
});

test('authorize returns false when meal plan does not exist', function () {
    $this->request->merge(['meal_plan_id' => 999]);

    expect($this->request->authorize())->toBeFalse();
});

test('authorize returns false when meal_plan_id is not provided', function () {
    expect($this->request->authorize())->toBeFalse();
});
