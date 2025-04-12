<?php

declare(strict_types=1);

use App\Models\MealPlan;
use App\Models\Recipe;
use App\Models\User;

test('user can add recipe to meal plan', function () {
    $user = User::factory()->create();
    $meal_plan = MealPlan::factory()->create(['user_id' => $user->id]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('meal-plans.add-recipe'), [
        'meal_plan_id' => $meal_plan->id,
        'recipe_id' => $recipe->id,
        'scale_factor' => 1.5,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Recipe added to meal plan successfully.'
        ]);

    expect(
        $meal_plan->recipes()
            ->where('recipe_id', $recipe->id)
            ->where('scale_factor', 1.5)
            ->exists()
    )->toBeTrue();
});

test('user cannot add recipe to meal plan of another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $meal_plan = MealPlan::factory()->create(['user_id' => $user1->id]);
    $recipe = Recipe::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user2)->post(route('meal-plans.add-recipe'), [
        'meal_plan_id' => $meal_plan->id,
        'recipe_id' => $recipe->id,
        'scale_factor' => 1.0,
    ]);

    $response->assertStatus(403);
    expect(
        $meal_plan->recipes()
            ->where('recipe_id', $recipe->id)
            ->exists()
    )->toBeFalse();
});

test('meal plan id is required when adding recipe', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('meal-plans.add-recipe'), [
        'recipe_id' => $recipe->id,
        'scale_factor' => 1.0,
    ]);

    $response->assertSessionHasErrors('meal_plan_id');
});

test('recipe id is required when adding recipe', function () {
    $user = User::factory()->create();
    $meal_plan = MealPlan::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('meal-plans.add-recipe'), [
        'meal_plan_id' => $meal_plan->id,
        'scale_factor' => 1.0,
    ]);

    $response->assertSessionHasErrors('recipe_id');
});

test('scale factor must be numeric', function () {
    $user = User::factory()->create();
    $meal_plan = MealPlan::factory()->create(['user_id' => $user->id]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('meal-plans.add-recipe'), [
        'meal_plan_id' => $meal_plan->id,
        'recipe_id' => $recipe->id,
        'scale_factor' => 'not-a-number',
    ]);

    $response->assertSessionHasErrors('scale_factor');
});

test('user can remove recipe from meal plan', function () {
    $user = User::factory()->create();
    $meal_plan = MealPlan::factory()->create(['user_id' => $user->id]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    // First add the recipe to the meal plan
    $meal_plan->recipes()->attach($recipe->id, ['scale_factor' => 1.0]);

    // Use our debug route instead
    $routeUrl = route('meal-plans.remove-recipe', ['id' => $meal_plan->id, 'recipeId' => $recipe->id]);

    $response = $this->actingAs($user)
                     ->from('/previous-page')
                     ->delete($routeUrl);

    $response->assertRedirect('/previous-page');
    expect(
        $meal_plan->recipes()
            ->where('recipe_id', $recipe->id)
            ->exists()
    )->toBeFalse();
});

test('user cannot remove recipe from meal plan of another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $meal_plan = MealPlan::factory()->create(['user_id' => $user1->id]);
    $recipe = Recipe::factory()->create(['user_id' => $user1->id]);

    // First add the recipe to the meal plan
    $meal_plan->recipes()->attach($recipe->id, ['scale_factor' => 1.0]);

    // Use our debug route instead
    $routeUrl = route('meal-plans.remove-recipe', ['id' => $meal_plan->id, 'recipeId' => $recipe->id]);

    $response = $this->actingAs($user2)->delete($routeUrl);

    $response->assertStatus(403);
    expect(
        $meal_plan->recipes()
            ->where('recipe_id', $recipe->id)
            ->exists()
    )->toBeTrue();
});

test('unauthenticated user cannot add recipe to meal plan', function () {
    $user = User::factory()->create();
    $meal_plan = MealPlan::factory()->create(['user_id' => $user->id]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->post(route('meal-plans.add-recipe'), [
        'meal_plan_id' => $meal_plan->id,
        'recipe_id' => $recipe->id,
        'scale_factor' => 1.0,
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot remove recipe from meal plan', function () {
    $user = User::factory()->create();
    $meal_plan = MealPlan::factory()->create(['user_id' => $user->id]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    // First add the recipe to the meal plan
    $meal_plan->recipes()->attach($recipe->id, ['scale_factor' => 1.0]);

    // Use our debug route instead
    $routeUrl = route('meal-plans.remove-recipe', ['id' => $meal_plan->id, 'recipeId' => $recipe->id]);

    $response = $this->delete($routeUrl);

    $response->assertRedirect(route('login'));
});

test('available servings are calculated when adding recipe', function () {
    $user = User::factory()->create();
    $meal_plan = MealPlan::factory()->create([
        'user_id' => $user->id,
        'people_count' => 2,
    ]);
    $recipe = Recipe::factory()->create([
        'user_id' => $user->id,
        'servings' => 4,
    ]);

    $response = $this->actingAs($user)->post(route('meal-plans.add-recipe'), [
        'meal_plan_id' => $meal_plan->id,
        'recipe_id' => $recipe->id,
        'scale_factor' => 1.0,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true
        ]);

    expect(
        $meal_plan->recipes()
            ->where('recipe_id', $recipe->id)
            ->where('servings_available', 2.0)
            ->exists()
    )->toBeTrue();
});

test('available servings are zero when plan has no people', function () {
    $user = User::factory()->create();
    $meal_plan = MealPlan::factory()->create([
        'user_id' => $user->id,
        'people_count' => 0,
    ]);
    $recipe = Recipe::factory()->create([
        'user_id' => $user->id,
        'servings' => 4,
    ]);

    $response = $this->actingAs($user)->post(route('meal-plans.add-recipe'), [
        'meal_plan_id' => $meal_plan->id,
        'recipe_id' => $recipe->id,
        'scale_factor' => 1.0,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true
        ]);

    expect(
        $meal_plan->recipes()
            ->where('recipe_id', $recipe->id)
            ->where('servings_available', 0.0)
            ->exists()
    )->toBeTrue();
});
