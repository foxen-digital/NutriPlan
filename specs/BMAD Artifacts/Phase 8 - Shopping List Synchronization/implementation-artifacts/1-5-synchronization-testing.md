# Story 1.5: Synchronization Testing

Status: review

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a development team,
I want comprehensive tests for the synchronization flow,
So that we can verify the feature works correctly and maintains quality standards.

## Acceptance Criteria

1. **Given** the ShoppingListSyncService, **When** unit tests are written, **Then** the service correctly identifies lists by meal_plan_id **And** the service dispatches the correct number of jobs **And** the service handles empty list results gracefully

2. **Given** the MealAssignmentObserver, **When** unit tests are written, **Then** the observer triggers on meal creation **And** the observer skips meals not marked for cooking **And** the observer skips meal updates (only creates trigger)

3. **Given** the UpdateShoppingListJob, **When** unit tests are written, **Then** existing ingredients have quantities incremented **And** new ingredients create list items **And** the job dispatches ShoppingListUpdated event on completion

4. **Given** the full synchronization flow, **When** integration tests are written, **Then** adding a new "to cook" meal updates all affected shopping lists **And** adding a meal to a plan with no linked shopping lists does not update any lists **And** adding a meal marked as leftover (to_cook=false) does not trigger updates **And** multiple overlapping lists all receive updates independently

5. **Given** the addition-only constraint, **When** tests verify edge cases, **Then** updating an existing meal does not trigger synchronization **And** deleting a meal does not remove ingredients from lists **And** toggling cooking flag off (via update) does not remove ingredients

6. **Given** performance requirements (NFR1, NFR4), **When** performance is validated, **Then** synchronization notes are documented (automated performance tests are not required — manual verification acceptable)

## Tasks / Subtasks

- [x] Create end-to-end integration test file: `tests/Feature/MealAssignmentSyncTest.php` (AC: 4)
  - [x] Test: adding a "to cook" meal updates all shopping lists linked to the same meal plan (sync queue)
  - [x] Test: adding a meal to a meal plan with no linked shopping lists creates no items
  - [x] Test: adding a meal with to_cook=false (leftover) creates no items (full flow)
  - [x] Test: multiple shopping lists linked to same meal plan all receive independent updates
  - [x] Test: two meals added to same plan → both sets of ingredients appear on the shopping list

- [x] Add addition-only edge case integration tests (AC: 5)
  - [x] Test: updating an existing MealAssignment does not modify shopping list items
  - [x] Test: deleting a MealAssignment does not remove any ingredients from the shopping list
  - [x] Test: toggling to_cook from true to false via update does not remove ingredients

- [x] Verify all existing unit tests pass (AC: 1, 2, 3)
  - [x] Run `php artisan test --compact tests/Feature/Services/ShoppingListSyncServiceTest.php`
  - [x] Run `php artisan test --compact tests/Feature/MealAssignmentObserverTest.php`
  - [x] Run `php artisan test --compact tests/Feature/Jobs/UpdateShoppingListJobTest.php`

- [x] Run full test suite for sync flow
  - [x] Run `php artisan test --compact --filter=Sync`
  - [x] Run `composer test:php` to confirm no regressions

## Dev Notes

### What Already Exists (Do NOT re-implement)

The following unit/feature tests were implemented as part of Stories 1.2, 1.3, and 1.4 and are **already passing**:

**`tests/Feature/MealAssignmentObserverTest.php`** — 5 tests covering:
- Observer triggers `ShoppingListSyncService` when `to_cook = true`
- Observer skips when `to_cook = false`
- Observer skips on `update` event
- Observer skips on `delete` event
- Observer pre-loads relationships before calling service

**`tests/Feature/Services/ShoppingListSyncServiceTest.php`** — 6 tests covering:
- Finds shopping lists by `meal_plan_id`
- Dispatches correct number of `UpdateShoppingListJob` instances
- Handles empty list results (no dispatch, logs info)
- Logging includes structured context
- Dispatches job with correct parameters (shoppingList + meal)
- Only dispatches for lists matching the meal plan (isolation from other plans)
- Handles missing `mealPlanDay` relationship gracefully

**`tests/Feature/Jobs/UpdateShoppingListJobTest.php`** — 12 tests covering:
- Existing ingredient (same unit) → quantity incremented
- Existing ingredient (different unit) → new item created
- New ingredient → new ShoppingListItem created with correct attributes
- ShoppingListUpdated event dispatched on success
- Recipe with no ingredients → no items, event still dispatched
- Multiple ingredients → handled correctly (mix of increment and create)
- Order for new items set at end of list
- Missing recipe relationship → no exception, no items, no event
- `failed()` method logs error with structured context
- Ingredients with zero/null amount → skipped
- Eager loading via `loadMissing()` prevents N+1
- Retry configuration: `$tries = 3`, `$backoff = [5, 15, 30]`

### What Needs to Be Created

**`tests/Feature/MealAssignmentSyncTest.php`** — End-to-end integration tests

This file was specified in `architecture.md` (Project Structure section, line 668) but was NOT created during Stories 1.2–1.4. Story 1.5 is specifically for creating this integration layer.

**Purpose:** Tests the **full async pipeline** end-to-end using `config(['queue.default' => 'sync'])` so jobs execute synchronously inline, allowing assertion on final database state.

### Architecture Flow (Full Pipeline)

```
User creates MealAssignment (to_cook=true)
         ↓
MealAssignmentObserver@created fires
         ↓
ShoppingListSyncService@syncNewMeal called
         ↓
UpdateShoppingListJob dispatched (one per matching ShoppingList)
         ↓ [sync queue — runs immediately in tests]
UpdateShoppingListJob@handle executes
         ↓
ShoppingListItem records inserted/incremented
         ↓
ShoppingListUpdated event dispatched
```

### Critical: Sync Queue vs Fake Queue

The existing tests in Stories 1.2–1.4 use `Bus::fake()` or `Queue::fake()` to **isolate** unit tests. The integration tests in this story must let jobs actually execute:

```php
// ✅ Integration test: use sync queue so jobs run inline
config(['queue.default' => 'sync']);

// OR use withoutDelay() + asserting DB state:
// The key is that UpdateShoppingListJob must actually run, not just be dispatched
```

Do NOT use `Bus::fake()` or `Queue::fake()` in integration tests — those prevent actual execution.

The existing `UpdateShoppingListJobTest.php` calls `$job->handle()` directly. For integration tests, letting the sync queue run the jobs is preferable to calling handle() directly.

### Key Model Relationships for Integration Tests

```php
// MealPlan links to ShoppingList via meal_plan_id:
ShoppingList::where('meal_plan_id', $mealPlan->id)->get()

// MealAssignment relationship chain for ingredients:
MealAssignment
  → mealPlanDay (BelongsTo: MealPlanDay)
      → mealPlan (BelongsTo: MealPlan)
  → mealPlanRecipe (BelongsTo: MealPlanRecipe)
      → recipe (BelongsTo: Recipe)
          → ingredients (BelongsToMany: Ingredient via recipe_ingredient pivot)
              pivot: amount (decimal), unit (string), description (nullable string)
```

### MealPlanRecipe Setup Pattern (Critical for factories)

The `MealPlanRecipe` pivot is created via `$mealPlan->recipes()->attach()`. Look at `ShoppingListSyncServiceTest.php` for the correct pattern:

```php
$mealPlanRecipe = MealPlanRecipe::create([
    'meal_plan_id' => $this->mealPlan->id,
    'recipe_id' => $this->recipe->id,
]);
```

Alternatively using attach:
```php
$mealPlan->recipes()->attach($recipe->id, ['scale_factor' => 1.0]);
$mealPlanRecipe = MealPlanRecipe::where('meal_plan_id', $mealPlan->id)
    ->where('recipe_id', $recipe->id)
    ->first();
```

### MealAssignment Factory Constraints

Creating a `MealAssignment` triggers the `MealAssignmentObserver`. To create one without triggering the observer (during test setup):

```php
$assignment = MealAssignment::withoutEvents(function () use ($mealPlanDay, $mealPlanRecipe) {
    return MealAssignment::factory()->create([...]);
});
```

To allow the observer to fire (for integration tests), create normally:
```php
$assignment = MealAssignment::factory()->create([
    'meal_plan_day_id' => $mealPlanDay->id,
    'meal_plan_recipe_id' => $mealPlanRecipe->id,
    'to_cook' => true,
]);
```

**Important:** `MealPlanRecipe::servings_available` must be decremented before creating an assignment (see `MealAssignmentObserverTest.php` lines 53–54 for the pattern).

### Integration Test Structure Example

```php
test('adding a to_cook meal updates all shopping lists linked to the meal plan', function () {
    config(['queue.default' => 'sync']);

    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user->id, 'people_count' => 2]);
    $recipe = Recipe::factory()->create(['servings' => 4]);

    // Link recipe to meal plan
    $mealPlan->recipes()->attach($recipe->id, ['scale_factor' => 1.0]);
    $mealPlanRecipe = MealPlanRecipe::where('meal_plan_id', $mealPlan->id)->first();

    $ingredient = Ingredient::factory()->create(['name' => 'Butter']);
    $recipe->ingredients()->attach($ingredient->id, ['amount' => 50.0, 'unit' => 'g']);

    // Link two shopping lists to this meal plan
    $list1 = ShoppingList::factory()->create(['user_id' => $user->id, 'meal_plan_id' => $mealPlan->id]);
    $list2 = ShoppingList::factory()->create(['user_id' => $user->id, 'meal_plan_id' => $mealPlan->id]);

    $mealPlanDay = MealPlanDay::factory()->create(['meal_plan_id' => $mealPlan->id]);

    // Adjust servings before creating assignment
    $mealPlanRecipe->servings_available -= 1.0;
    $mealPlanRecipe->save();

    // Act: Create meal assignment (triggers observer → service → job → item update)
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $mealPlanDay->id,
        'meal_plan_recipe_id' => $mealPlanRecipe->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    // Assert: Both shopping lists have the ingredient added
    expect($list1->fresh()->items()->count())->toBe(1)
        ->and($list2->fresh()->items()->count())->toBe(1)
        ->and((float) $list1->items()->first()->quantity)->toBe(50.0)
        ->and((float) $list2->items()->first()->quantity)->toBe(50.0);
});
```

### Addition-Only Constraint Test Pattern

```php
test('deleting a meal assignment does not remove ingredients from shopping list', function () {
    config(['queue.default' => 'sync']);

    // ... setup meal, list, items ...

    // Create the meal (adds ingredients)
    $assignment = MealAssignment::factory()->create([...]);

    // Verify items were added
    expect($shoppingList->fresh()->items()->count())->toBe(1);

    // Delete the assignment
    $assignment->delete();

    // Assert: Items NOT removed (addition-only)
    expect($shoppingList->fresh()->items()->count())->toBe(1);
    expect((float) $shoppingList->items()->first()->quantity)->toBe(50.0); // unchanged
});

test('updating a meal assignment to_cook=false does not remove ingredients', function () {
    config(['queue.default' => 'sync']);

    // ... setup with existing items after initial creation ...

    // Update to_cook to false
    $assignment->update(['to_cook' => false]);

    // Assert: Items NOT removed (addition-only, observer only fires on created)
    expect($shoppingList->fresh()->items()->count())->toBeGreaterThan(0);
});
```

### File Structure Requirements

**Files to CREATE:**
- `tests/Feature/MealAssignmentSyncTest.php` — End-to-end integration tests

**Files NOT to modify:**
- `tests/Feature/MealAssignmentObserverTest.php` — Complete from Story 1.2
- `tests/Feature/Services/ShoppingListSyncServiceTest.php` — Complete from Story 1.3
- `tests/Feature/Jobs/UpdateShoppingListJobTest.php` — Complete from Story 1.4

### Testing Standards

- Use `test()` function (project standard, Pest 3)
- Use `it()` where it reads more naturally (both styles are used in the codebase)
- `declare(strict_types=1);` at top of file
- Use factories for all test data — never raw `DB::insert()`
- Feature tests are database tests (use `RefreshDatabase` trait via `Pest.php`)
- Run `composer test:php` before marking complete (maps to `php artisan test --compact`)
- Run `vendor/bin/pint --dirty --format agent` after modifying any PHP files

### Performance Requirements Reference (NFR1, NFR4)

- NFR1: Updates complete within 5 seconds for typical scenarios
- NFR4: 95th percentile of background list processing under 2 seconds

These are not automated in the test suite. Verify manually by running `composer dev` and timing actual list updates with a queue worker. Document observations in the Dev Agent Record completion notes.

### Project Structure Notes

- Integration test location follows architecture.md specification: `tests/Feature/MealAssignmentSyncTest.php`
- Namespace: no explicit namespace for feature tests (see `tests/Feature/ShoppingListTest.php` pattern — use `Tests\Feature` only if required)
- Pest config: check `tests/Pest.php` for base test case setup

### References

- Epic 1 Story 1.5 Requirements [Source: _bmad-output/planning-artifacts/epics.md#Story 1.5 lines 267–311]
- Architecture: End-to-end sync test file [Source: _bmad-output/planning-artifacts/architecture.md#Project Structure & Boundaries line 668]
- Architecture: Event flow [Source: _bmad-output/planning-artifacts/architecture.md#Event System Design lines 212–226]
- Architecture: Addition-only constraint [Source: _bmad-output/planning-artifacts/architecture.md#Core Architectural Decisions lines 178–190]
- Existing observer tests [Source: tests/Feature/MealAssignmentObserverTest.php]
- Existing service tests [Source: tests/Feature/Services/ShoppingListSyncServiceTest.php]
- Existing job tests [Source: tests/Feature/Jobs/UpdateShoppingListJobTest.php]
- Story 1.4 dev notes (job/event patterns) [Source: _bmad-output/implementation-artifacts/1-4-background-list-update-job.md]

## Dev Agent Record

### Agent Model Used

claude-sonnet-4-6

### Debug Log References

None — all tests passed on first run.

### Completion Notes List

- Created `tests/Feature/MealAssignmentSyncTest.php` with 10 end-to-end integration tests covering AC4 and AC5.
- Used `config(['queue.default' => 'sync'])` so jobs run inline, enabling DB-state assertions after MealAssignment creation.
- Used `Event::fake([ShoppingListUpdated::class])` to prevent actual broadcasting while still asserting event dispatch.
- All 10 new integration tests pass (43 assertions).
- All 24 existing unit tests for Observer, Service, and Job continue to pass.
- Full suite: 573 passed, 0 failed (2247 assertions). Zero regressions.
- Pint style check: pass.
- AC6 (performance) documented as manual verification — no automated performance tests added per story spec.

### File List

- tests/Feature/MealAssignmentSyncTest.php (created)
