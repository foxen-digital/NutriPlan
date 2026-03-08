# Story 1.3: Shopping List Sync Service

Status: done
<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a system,
I want a service that identifies affected shopping lists and dispatches update jobs,
So that list synchronization happens efficiently in the background.

## Acceptance Criteria

1. **Given** a new meal assignment marked for cooking, **When** the ShoppingListSyncService@syncNewMeal is invoked, **Then** the service retrieves the meal plan from the meal's relationships
2. **And** queries for all shopping lists with matching meal_plan_id
3. **Given** multiple shopping lists exist for the same meal plan, **When** the service finds affected lists, **Then** one UpdateShoppingListJob is dispatched for each shopping list
4. **And** jobs are dispatched with the shopping list and meal assignment
5. **Given** no shopping lists exist for the meal plan, **When** the service queries for lists, **Then** no jobs are dispatched
6. **And** the service completes without error
7. **Given** the service dispatches jobs, **When** logging occurs, **Then** the number of affected lists is logged with meal assignment context
8. **And** structured logging includes meal_plan_id and list count

## Tasks / Subtasks

- [x] Implement syncNewMeal() method in ShoppingListSyncService (AC: 1, 2)
  - [x] Get meal_plan_id from `$meal->mealPlanDay->mealPlan->id` (relationships pre-loaded by observer)
  - [x] Query shopping lists: `ShoppingList::where('meal_plan_id', $mealPlanId)->get()`
  - [x] Use the user_id from the meal plan for additional filtering if needed
- [x] Dispatch UpdateShoppingListJob for each affected list (AC: 3, 4)
  - [x] Create UpdateShoppingListJob class stub (actual implementation in Story 1.4)
  - [x] Loop through affected lists and dispatch job for each
  - [x] Pass shopping list and meal assignment to job constructor
- [x] Handle empty list scenario gracefully (AC: 5, 6)
  - [x] If no shopping lists found, log info and return without error
  - [x] No exceptions thrown for empty results
- [x] Add structured logging (AC: 7, 8)
  - [x] Log info with meal_plan_id and count of affected lists
  - [x] Use Laravel's Log facade with context array
- [x] Write unit tests for ShoppingListSyncService
  - [x] Test service finds lists by meal_plan_id
  - [x] Test service dispatches correct number of jobs
  - [x] Test service handles empty list results gracefully
  - [x] Test logging includes correct context
- [x] Run `composer test:php` to ensure no regressions

## Dev Notes

### Architecture Context

This service is the **orchestration layer** between the observer (Story 1.2) and the background jobs (Story 1.4). Its sole responsibility is to identify affected shopping lists and dispatch jobs for asynchronous processing.

**Event Flow Position:**
```
MealAssignment::created
         ↓
MealAssignmentObserver@created (already loads relationships)
         ↓
ShoppingListSyncService@syncNewMeal ← THIS STORY
         ↓
UpdateShoppingListJob::dispatch (one per list)
         ↓
Queue Worker → Update ingredients
```

**Critical Design Decisions:**
- **Single Responsibility:** This service ONLY finds lists and dispatches jobs - NO ingredient processing logic
- **Job per List:** Each shopping list gets its own job for parallel processing and failure isolation
- **Graceful Degradation:** No shopping lists = no error, just log and return

### Previous Story Intelligence

**From Story 1.1 (Database Schema Addition):**
- `meal_plan_id` column exists in `shopping_lists` table
- `ShoppingList::mealPlan()` relationship is available
- Column is nullable - existing lists may not have meal_plan_id set

**From Story 1.2 (Meal Creation Observer):**
- Observer already exists and invokes this service
- Relationships are pre-loaded: `$meal->load('mealPlanDay.mealPlan', 'mealPlanRecipe.recipe.ingredients')`
- Service method signature: `syncNewMeal(MealAssignment $meal): void`
- Observer registration is complete in AppServiceProvider

**Patterns Established:**
- All PHP files start with `declare(strict_types=1);` at line 1
- Type hints on all method signatures and return types
- PSR-12 code style via Laravel Pint
- Use Pest `test()` function, NOT `@test` annotation

### Relevant Architecture Patterns and Constraints

**Service Layer Design (architecture.md lines 227-242):**
```php
class ShoppingListSyncService
{
    public function syncNewMeal(MealAssignment $meal): void
    // 1. Validate meal is marked for cooking (already done by observer)
    // 2. Get meal plan from meal->mealPlanDay->mealPlan
    // 3. Find all shopping lists for this meal plan
    // 4. Dispatch job for each list
}
```

**Single Responsibility (architecture.md line 240):**
Encapsulates all synchronization logic in one focused service

**Query Pattern (architecture.md lines 517-522):**
```php
// Indexed query for performance
ShoppingList::where('meal_plan_id', $mealPlanId)
    ->where('user_id', $userId)  // Optional: for security
    ->get();
```

**Logging Pattern (architecture.md lines 498-506):**
```php
// Structured logging with context
Log::info('Shopping list sync initiated', [
    'meal_assignment_id' => $meal->id,
    'meal_plan_id' => $mealPlanId,
    'affected_lists' => $shoppingLists->count(),
]);
```

### Source Tree Components to Touch

**Files to MODIFY:**
1. `app/Services/ShoppingListSyncService.php` - Implement syncNewMeal() method

**Files to CREATE:**
1. `app/Jobs/UpdateShoppingListJob.php` - Stub for Story 1.4 (needed for dispatch)
2. `tests/Unit/Services/ShoppingListSyncServiceTest.php` - Unit tests

### Testing Standards Summary

**Pest PHP Testing (from project-context.md lines 107-135):**
- Use `test()` function, NOT `@test` annotation
- Use factories for data creation
- Mock the UpdateShoppingListJob dispatch to test service in isolation
- Run `composer test` before marking complete

**Required Test Coverage:**
- Service correctly identifies lists by meal_plan_id
- Service dispatches the correct number of jobs
- Service handles empty list results gracefully (no exception)
- Logging includes correct context (meal_plan_id, list count)
- Job dispatch includes correct parameters (shopping list, meal assignment)

**Testing Pattern:**
```php
// Use Bus::fake() to test job dispatch
Bus::fake();

// Create meal assignment with relationships
$mealPlan = MealPlan::factory()->create();
$mealPlanDay = MealPlanDay::factory()->for($mealPlan)->create();
$meal = MealAssignment::factory()->for($mealPlanDay)->create(['to_cook' => true]);

// Create shopping lists for the meal plan
ShoppingList::factory()->count(3)->for($user)->create(['meal_plan_id' => $mealPlan->id]);

// Run service
app(ShoppingListSyncService::class)->syncNewMeal($meal);

// Assert jobs dispatched
Bus::assertDispatchedTimes(UpdateShoppingListJob::class, 3);
```

### Project Structure Notes

**Alignment with unified project structure:**
- Service follows existing pattern in `app/Services/`
- Job stub follows existing pattern in `app/Jobs/` (like `ImportRecipeJob.php`)
- Unit tests go in `tests/Unit/Services/`

**Detected conflicts or variances:** None identified - follows established patterns.

### Dependencies

**Requires Story 1.1 (COMPLETE):**
- ✅ ShoppingList model has `mealPlan()` relationship
- ✅ meal_plan_id column exists in shopping_lists table

**Requires Story 1.2 (COMPLETE):**
- ✅ MealAssignmentObserver exists and invokes this service
- ✅ Observer loads relationships before calling service

**Blocks Story 1.4 (Background List Update Job):**
- This story creates UpdateShoppingListJob stub
- Story 1.4 implements the actual job logic

### Critical Implementation Notes

**Relationship Access:**
The observer pre-loads relationships, so access them directly:
```php
// Relationships already loaded by observer
$mealPlanId = $meal->mealPlanDay->mealPlan->id;
```

**Job Stub Creation:**
Create minimal stub for UpdateShoppingListJob that Story 1.4 will implement:
```php
class UpdateShoppingListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly ShoppingList $shoppingList,
        public readonly MealAssignment $meal
    ) {}

    public function handle(): void
    {
        // Implementation in Story 1.4
    }
}
```

**Edge Cases to Handle:**
1. `meal_plan_id` is null on some shopping lists (legacy data)
2. No shopping lists exist for the meal plan
3. Multiple shopping lists for same meal plan (all should get jobs)

### References

- **Service Layer Architecture** [Source: _bmad-output/planning-artifacts/architecture.md#Service Layer Design lines 227-242]
- **Query Pattern** [Source: _bmad-output/planning-artifacts/architecture.md#Database Query Pattern lines 517-522]
- **Logging Pattern** [Source: _bmad-output/planning-artifacts/architecture.md#Logging Pattern lines 498-506]
- **Epic 1 Story 1.3 Requirements** [Source: _bmad-output/planning-artifacts/epics.md#Story 1.3 lines 192-219]
- **Existing Service Stub** [Source: app/Services/ShoppingListSyncService.php]
- **Observer Implementation** [Source: app/Observers/MealAssignmentObserver.php]
- **ShoppingList Model** [Source: app/Models/ShoppingList.php]
- **Project Context - PHP Rules** [Source: _bmad-output/project-context.md#Language-Specific Rules PHP lines 62-68]
- **Project Context - Testing** [Source: _bmad-output/project-context.md#Testing Rules lines 107-135]

## Dev Agent Record

### Agent Model Used

Claude (claude-sonnet-4.6)

### Debug Log References

None - implementation proceeded smoothly.

### Completion Notes List

1. **Implemented syncNewMeal() method** - Service finds shopping lists by meal_plan_id and dispatches UpdateShoppingListJob for each affected list.
2. **Created UpdateShoppingListJob stub** - Minimal job stub with constructor accepting ShoppingList and MealAssignment. Actual logic implemented in Story 1.4.
3. **Graceful empty handling** - Returns gracefully when no shopping lists found, with info logging.
4. **Structured logging** - Uses Log::info with context including meal_assignment_id, meal_plan_id, and affected_lists count.
5. **Unit tests** - 6 tests covering all acceptance criteria: finding lists, dispatching jobs, empty scenario, logging context, job parameters, and selective dispatching.

### File List

- `app/Services/ShoppingListSyncService.php` (modified) - Implemented syncNewMeal() method
- `app/Jobs/UpdateShoppingListJob.php` (created) - Job stub for Story 1.4
- `tests/Feature/Services/ShoppingListSyncServiceTest.php` (created) - Service tests (moved from Unit; database integration tests belong in Feature)
- `tests/Feature/MealAssignmentObserverTest.php` (modified) - Updated observer tests

### Change Log

- 2026-03-07: Implemented ShoppingListSyncService@syncNewMeal method with job dispatch and logging
- 2026-03-07: Code review fixes — added null safety guard on relationship chain, user_id security filter on ShoppingList query, Log::info on empty list case, moved tests from Unit to Feature with beforeEach refactor and full logging assertions
