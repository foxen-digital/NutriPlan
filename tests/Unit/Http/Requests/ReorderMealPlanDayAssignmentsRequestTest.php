<?php

declare(strict_types=1);

use App\Http\Requests\ReorderMealPlanDayAssignmentsRequest;
use App\Models\MealAssignment;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\MealPlanRecipe;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

beforeEach(function() {
    // Set up test data
    $this->owner = User::factory()->create();
    $this->otherUser = User::factory()->create();
    
    $this->mealPlan = MealPlan::factory()->create([
        'user_id' => $this->owner->id
    ]);
    
    $this->mealPlanDay = MealPlanDay::factory()
        ->create([
            'meal_plan_id' => $this->mealPlan->id,
            'day_number' => 1
        ]);

    // Create recipes and meal plan recipes
    $recipes = Recipe::factory(3)->create();
    $this->mealPlanRecipes = collect();
    
    foreach ($recipes as $recipe) {
        $this->mealPlanRecipes->push(
            MealPlanRecipe::factory()->create([
                'meal_plan_id' => $this->mealPlan->id,
                'recipe_id' => $recipe->id
            ])
        );
    }

    // Create assignments
    $this->assignments = collect();
    foreach ($this->mealPlanRecipes as $index => $mealPlanRecipe) {
        $this->assignments->push(
            MealAssignment::factory()->create([
                'meal_plan_day_id' => $this->mealPlanDay->id,
                'meal_plan_recipe_id' => $mealPlanRecipe->id,
                'order' => $index
            ])
        );
    }

    $this->assignmentIds = $this->assignments->pluck('id')->toArray();
    
    // Shuffle the ids for a valid reorder request
    $this->shuffledIds = $this->assignmentIds;
    shuffle($this->shuffledIds);
    
    $this->validParams = [
        'assignment_ids' => $this->shuffledIds
    ];
});

test('authorization passes if user owns the meal plan', function () {
    $request = new ReorderMealPlanDayAssignmentsRequest();
    $request->setRouteResolver(function () {
        return Route::getRoutes()->getRoutesByMethod()['POST']['/test-route/{meal_plan_day}'] ?? null;
    });
    
    $request->merge($this->validParams);
    $request->setUserResolver(fn () => $this->owner);
    $request->mealPlanDay = $this->mealPlanDay;
    
    expect($request->authorize())->toBeTrue();
});

test('authorization fails if user does not own the meal plan', function () {
    $request = new ReorderMealPlanDayAssignmentsRequest();
    $request->setRouteResolver(function () {
        return Route::getRoutes()->getRoutesByMethod()['POST']['/test-route/{meal_plan_day}'] ?? null;
    });
    
    $request->merge($this->validParams);
    $request->setUserResolver(fn () => $this->otherUser);
    $request->mealPlanDay = $this->mealPlanDay;
    
    expect($request->authorize())->toBeFalse();
});

test('validation passes with valid data', function () {
    $rules = (new ReorderMealPlanDayAssignmentsRequest())->rules();
    $validator = Validator::make($this->validParams, $rules);
    
    expect($validator->fails())->toBeFalse();
});

test('validation fails if assignment_ids not provided', function () {
    $rules = (new ReorderMealPlanDayAssignmentsRequest())->rules();
    $validator = Validator::make([], $rules);
    
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('assignment_ids'))->toBeTrue();
});

test('validation fails if assignment_ids not an array', function () {
    $rules = (new ReorderMealPlanDayAssignmentsRequest())->rules();
    $validator = Validator::make(['assignment_ids' => 'not-an-array'], $rules);
    
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('assignment_ids'))->toBeTrue();
});

test('validation fails if assignment_ids contains non_integers', function () {
    $rules = (new ReorderMealPlanDayAssignmentsRequest())->rules();
    
    $invalidIds = $this->assignmentIds;
    $invalidIds[1] = 'not-an-integer';
    
    $validator = Validator::make(['assignment_ids' => $invalidIds], $rules);
    
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('assignment_ids.1'))->toBeTrue();
});

test('validation fails if assignment_id does not exist', function () {
    $rules = (new ReorderMealPlanDayAssignmentsRequest())->rules();
    
    $invalidIds = $this->assignmentIds;
    $invalidIds[1] = 9999; // Non-existent ID
    
    $validator = Validator::make(['assignment_ids' => $invalidIds], $rules);
    
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('assignment_ids.1'))->toBeTrue();
});

test('validates assignment IDs belong to the specified meal plan day', function () {
    // Register the routes for testing
    Route::post('/test-route/{meal_plan_day}', function () {
        return 'OK';
    })->name('test.route');
    
    // Create a mock object that will validate the assignment IDs
    $request = new class extends ReorderMealPlanDayAssignmentsRequest {
        public bool $validationCalled = false;
        
        protected function validateAssignmentIds(): void
        {
            $this->validationCalled = true;
        }
    };
    
    $request->setRouteResolver(function () {
        return Route::getRoutes()->getRoutesByMethod()['POST']['/test-route/{meal_plan_day}'] ?? null;
    });
    
    $request->merge($this->validParams);
    $request->setUserResolver(fn () => $this->owner);
    $request->mealPlanDay = $this->mealPlanDay;
    
    // Call the after method directly
    $afterCallbacks = $request->after();
    foreach ($afterCallbacks as $callback) {
        $callback();
    }
    
    // Check that the validateAssignmentIds method was called
    expect($request->validationCalled)->toBeTrue();
});             