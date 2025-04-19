<?php

use App\Models\ShoppingList;
use App\Models\User;
use App\Policies\ShoppingListPolicy;

beforeEach(function () {
    $this->policy = new ShoppingListPolicy();
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->shoppingList = ShoppingList::factory()->create([
        'user_id' => $this->user->id
    ]);
});

test('user can view any shopping lists', function () {
    expect($this->policy->viewAny($this->user))->toBeTrue();
});

test('user can view their own shopping list', function () {
    expect($this->policy->view($this->user, $this->shoppingList))->toBeTrue();
});

test('user cannot view other users shopping lists', function () {
    expect($this->policy->view($this->otherUser, $this->shoppingList))->toBeFalse();
});

test('user can create shopping lists', function () {
    expect($this->policy->create($this->user))->toBeTrue();
});

test('user can update their own shopping list', function () {
    expect($this->policy->update($this->user, $this->shoppingList))->toBeTrue();
});

test('user cannot update other users shopping lists', function () {
    expect($this->policy->update($this->otherUser, $this->shoppingList))->toBeFalse();
});

test('user can delete their own shopping list', function () {
    expect($this->policy->delete($this->user, $this->shoppingList))->toBeTrue();
});

test('user cannot delete other users shopping lists', function () {
    expect($this->policy->delete($this->otherUser, $this->shoppingList))->toBeFalse();
});

test('user can restore their own shopping list', function () {
    expect($this->policy->restore($this->user, $this->shoppingList))->toBeTrue();
});

test('user cannot restore other users shopping lists', function () {
    expect($this->policy->restore($this->otherUser, $this->shoppingList))->toBeFalse();
});

test('user can force delete their own shopping list', function () {
    expect($this->policy->forceDelete($this->user, $this->shoppingList))->toBeTrue();
});

test('user cannot force delete other users shopping lists', function () {
    expect($this->policy->forceDelete($this->otherUser, $this->shoppingList))->toBeFalse();
});
