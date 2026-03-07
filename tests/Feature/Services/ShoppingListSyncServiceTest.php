<?php

declare(strict_types=1);

use App\Jobs\UpdateShoppingListJob;
use App\Models\MealAssignment;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\MealPlanRecipe;
use App\Models\Recipe;
use App\Models\ShoppingList;
use App\Models\User;
use App\Services\ShoppingListSyncService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->service = new ShoppingListSyncService();
    $this->user = User::factory()->create();
    $this->mealPlan = MealPlan::factory()->create(['user_id' => $this->user->id]);
    $this->mealPlanDay = MealPlanDay::factory()->create(['meal_plan_id' => $this->mealPlan->id]);
    $this->recipe = Recipe::factory()->create(['user_id' => $this->user->id]);
    $this->mealPlanRecipe = MealPlanRecipe::create([
        'meal_plan_id' => $this->mealPlan->id,
        'recipe_id' => $this->recipe->id,
    ]);
    $this->meal = MealAssignment::factory()->create([
        'meal_plan_day_id' => $this->mealPlanDay->id,
        'meal_plan_recipe_id' => $this->mealPlanRecipe->id,
        'to_cook' => true,
    ]);
    $this->meal->load('mealPlanDay.mealPlan', 'mealPlanRecipe.recipe.ingredients');
});

test('syncNewMeal finds shopping lists by meal_plan_id', function () {
    Bus::fake();

    ShoppingList::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'meal_plan_id' => $this->mealPlan->id,
    ]);

    $this->service->syncNewMeal($this->meal);

    Bus::assertDispatchedTimes(UpdateShoppingListJob::class, 3);
});

test('syncNewMeal dispatches correct number of jobs', function () {
    Bus::fake();

    ShoppingList::factory()->count(5)->create([
        'user_id' => $this->user->id,
        'meal_plan_id' => $this->mealPlan->id,
    ]);

    $this->service->syncNewMeal($this->meal);

    Bus::assertDispatchedTimes(UpdateShoppingListJob::class, 5);
});

test('syncNewMeal handles empty list results gracefully', function () {
    Bus::fake();
    Log::spy();

    // No shopping lists for this meal plan
    $this->service->syncNewMeal($this->meal);

    Bus::assertNotDispatched(UpdateShoppingListJob::class);
    Log::shouldHaveReceived('info')
        ->once()
        ->with(
            \Mockery::on(fn (string $msg) => str_contains($msg, 'No shopping lists')),
            \Mockery::type('array')
        );
});

test('syncNewMeal logging includes correct context', function () {
    Bus::fake();
    Log::spy();

    ShoppingList::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'meal_plan_id' => $this->mealPlan->id,
    ]);

    $this->service->syncNewMeal($this->meal);

    Log::shouldHaveReceived('info')
        ->once()
        ->with(
            \Mockery::on(fn (string $msg) => str_contains($msg, 'Shopping list sync initiated')),
            \Mockery::on(function (array $context) {
                return isset($context['meal_assignment_id'])
                    && isset($context['meal_plan_id'])
                    && $context['affected_lists'] === 2;
            })
        );
});

test('syncNewMeal dispatches job with correct parameters', function () {
    Bus::fake();

    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
        'meal_plan_id' => $this->mealPlan->id,
    ]);

    $this->service->syncNewMeal($this->meal);

    Bus::assertDispatched(UpdateShoppingListJob::class, function (UpdateShoppingListJob $job) use ($shoppingList) {
        return $job->shoppingList->id === $shoppingList->id
            && $job->meal->id === $this->meal->id;
    });
});

test('syncNewMeal only dispatches jobs for lists matching meal_plan_id', function () {
    Bus::fake();

    $mealPlan2 = MealPlan::factory()->create(['user_id' => $this->user->id]);

    ShoppingList::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'meal_plan_id' => $this->mealPlan->id,
    ]);

    ShoppingList::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'meal_plan_id' => $mealPlan2->id,
    ]);

    $this->service->syncNewMeal($this->meal);

    Bus::assertDispatchedTimes(UpdateShoppingListJob::class, 2);
});

test('syncNewMeal handles missing meal plan relationship gracefully', function () {
    Bus::fake();
    Log::spy();

    $this->meal->setRelation('mealPlanDay', null);

    $this->service->syncNewMeal($this->meal);

    Bus::assertNotDispatched(UpdateShoppingListJob::class);
    Log::shouldHaveReceived('warning')->once();
});
