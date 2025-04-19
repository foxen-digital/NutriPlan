<?php

declare(strict_types=1);

use App\Models\MealPlan;
use App\Models\User;
use App\Policies\MealPlanPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new MealPlanPolicy();
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->mealPlan = MealPlan::factory()->create(['user_id' => $this->user->id]);
});

test('viewAny returns true for any user', function () {
    expect($this->policy->viewAny($this->user))->toBeTrue()
        ->and($this->policy->viewAny($this->otherUser))->toBeTrue();
});

test('view allows owner to view their meal plan', function () {
    expect($this->policy->view($this->user, $this->mealPlan))->toBeTrue();
});

test('view denies non-owner from viewing meal plan', function () {
    expect($this->policy->view($this->otherUser, $this->mealPlan))->toBeFalse();
});

test('create returns true for any user', function () {
    expect($this->policy->create($this->user))->toBeTrue()
        ->and($this->policy->create($this->otherUser))->toBeTrue();
});

test('update allows owner to update their meal plan', function () {
    expect($this->policy->update($this->user, $this->mealPlan))->toBeTrue();
});

test('update denies non-owner from updating meal plan', function () {
    expect($this->policy->update($this->otherUser, $this->mealPlan))->toBeFalse();
});

test('delete allows owner to delete their meal plan', function () {
    expect($this->policy->delete($this->user, $this->mealPlan))->toBeTrue();
});

test('delete denies non-owner from deleting meal plan', function () {
    expect($this->policy->delete($this->otherUser, $this->mealPlan))->toBeFalse();
});

test('restore allows owner to restore their meal plan', function () {
    expect($this->policy->restore($this->user, $this->mealPlan))->toBeTrue();
});

test('restore denies non-owner from restoring meal plan', function () {
    expect($this->policy->restore($this->otherUser, $this->mealPlan))->toBeFalse();
});

test('forceDelete allows owner to force delete their meal plan', function () {
    expect($this->policy->forceDelete($this->user, $this->mealPlan))->toBeTrue();
});

test('forceDelete denies non-owner from force deleting meal plan', function () {
    expect($this->policy->forceDelete($this->otherUser, $this->mealPlan))->toBeFalse();
});
