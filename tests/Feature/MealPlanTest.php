<?php

declare(strict_types=1);

use App\Models\MealPlan;
use App\Models\Recipe;
use App\Models\User;

test('authenticated user can access meal plans page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('meal-plans.index'));

    $response->assertStatus(200);
});

test('unauthenticated user cannot access meal plans page', function () {
    $response = $this->get(route('meal-plans.index'));

    $response->assertRedirect(route('login'));
});

test('user can create meal plan', function () {
    $user = User::factory()->create();
    $data = [
        'name' => 'Test Meal Plan',
        'start_date' => now()->format('Y-m-d'),
        'duration' => 7,
        'people_count' => 4,
    ];

    $response = $this->actingAs($user)->post(route('meal-plans.store'), $data);

    $response->assertRedirect(route('meal-plans.index'));

    $this->assertDatabaseHas('meal_plans', [
        'user_id' => $user->id,
        'name' => $data['name'],
        'duration' => $data['duration'],
        'people_count' => $data['people_count'],
    ]);
});

test('user can create meal plan without name', function () {
    $user = User::factory()->create();
    $data = [
        'start_date' => now()->format('Y-m-d'),
        'duration' => 7,
        'people_count' => 4,
    ];

    $response = $this->actingAs($user)->post(route('meal-plans.store'), $data);

    $response->assertRedirect(route('meal-plans.index'));

    $this->assertDatabaseHas('meal_plans', [
        'user_id' => $user->id,
        'name' => null,
        'duration' => $data['duration'],
        'people_count' => $data['people_count'],
    ]);
});

test('user can view meal plan', function () {
    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user->id]);

    // Add a recipe to the meal plan to test eager loading
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);
    $mealPlan->recipes()->attach($recipe->id, ['scale_factor' => 1.0]);

    $response = $this->actingAs($user)->get(route('meal-plans.show', $mealPlan));

    $response->assertStatus(200);

    $response->assertInertia(
        fn ($page) => $page
            ->component('MealPlans/Show')
            ->has('mealPlan')
            ->has('availableMealPlans')
    );
});

test('user cannot view meal plan of another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user1->id]);

    $response = $this->actingAs($user2)->get(route('meal-plans.show', $mealPlan));

    $response->assertStatus(403);
});

test('edit method returns null', function () {
    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user->id]);

    $controller = app(\App\Http\Controllers\MealPlan\MealPlanController::class);
    $result = $controller->edit((string) $mealPlan->id);

    expect($result)->toBeNull();
});

test('update method returns null', function () {
    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user->id]);

    $controller = app(\App\Http\Controllers\MealPlan\MealPlanController::class);
    $request = new \Illuminate\Http\Request();
    $result = $controller->update($request, (string) $mealPlan->id);

    expect($result)->toBeNull();
});

test('user can delete meal plan', function () {
    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('meal-plans.destroy', $mealPlan));

    $response->assertRedirect(route('meal-plans.index'));

    $this->assertDatabaseMissing('meal_plans', [
        'id' => $mealPlan->id,
    ]);
});

test('user cannot delete meal plan of another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user1->id]);

    $response = $this->actingAs($user2)->delete(route('meal-plans.destroy', $mealPlan));

    $response->assertStatus(403);

    $this->assertDatabaseHas('meal_plans', [
        'id' => $mealPlan->id,
    ]);
});
