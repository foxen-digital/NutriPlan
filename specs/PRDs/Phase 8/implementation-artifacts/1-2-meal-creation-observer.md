# Story 1.2: Meal Creation Observer

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a system,
I want to detect when new meals are added to a meal plan,
So that synchronization can be triggered for affected shopping lists.

## Acceptance Criteria

1. **Given** a MealAssignment model, **When** a new MealAssignment is created, **Then** the MealAssignmentObserver@created event is triggered
2. **And** the observer checks if the meal is marked for cooking (to_cook = true)
3. **Given** a meal marked as not for cooking (to_cook = false), **When** the meal is created, **Then** the observer returns without triggering synchronization
4. **And** no jobs are dispatched
5. **Given** a meal marked for cooking (to_cook = true), **When** the meal is created, **Then** the observer loads the MealPlanDay and MealPlan relationships
6. **And** the ShoppingListSyncService is invoked with the meal assignment
7. **Given** an existing meal is updated (not created), **When** the meal is modified, **Then** the observer does not trigger synchronization
8. **And** no jobs are dispatched

## Tasks / Subtasks

- [x] Create Observers directory and MealAssignmentObserver class (AC: 1)
  - [x] Create `app/Observers/` directory (first observer in project)
  - [x] Create MealAssignmentObserver class manually (Laravel artisan not used to keep strict_types declaration)
  - [x] Add `declare(strict_types=1);` at line 1
- [x] Implement created() method with to_cook check (AC: 2, 3, 4)
  - [x] Implement `created(MealAssignment $meal): void` method
  - [x] Check `$meal->to_cook` flag - return early if false
  - [x] Add test to verify no sync triggered when to_cook is false
- [x] Implement relationship loading and service invocation (AC: 5, 6)
  - [x] Eager load relationships: `$meal->load('mealPlanDay.mealPlan', 'mealPlanRecipe.recipe.ingredients')`
  - [x] Call `app(ShoppingListSyncService::class)->syncNewMeal($meal)`
  - [x] Add test to verify service is called with correct meal
- [x] Verify observer only triggers on creation, not updates (AC: 7, 8)
  - [x] Add test for MealAssignment::updated() to confirm no trigger
  - [x] Verify no updating() or updated() methods are implemented
- [x] Register observer in AppServiceProvider (Laravel 12 pattern)
  - [x] Add MealAssignment::observe(MealAssignmentObserver::class) in AppServiceProvider boot() method
  - [x] Verify observer is registered and fires on model creation
- [x] Run `composer test:unit` to ensure no regressions

## Dev Notes

### Architecture Context

This is the **first observer** in the NutriPlan project. The observer pattern enables decoupled event handling - when a meal is added to a plan, the observer automatically triggers synchronization without the controller needing to know about it.

**Critical Addition-Only Constraint:** The observer ONLY triggers on model creation (`created` event), NOT on updates or deletions. This ensures that:
- Editing an existing meal doesn't re-add ingredients to shopping lists
- Deleting a meal doesn't remove ingredients from shopping lists
- Toggling the cooking flag off doesn't trigger updates

**Event Flow:**
```
MealAssignment::created (Eloquent Event)
         ↓
MealAssignmentObserver@created
         ↓ (if to_cook = true)
ShoppingListSyncService@syncNewMeal
         ↓
Dispatch UpdateShoppingListJob for each affected list
```

### Previous Story Intelligence

**From Story 1.1 (Database Schema Addition):**
- The `meal_plan_id` column was added to `shopping_lists` table
- ShoppingList model now has `mealPlan()` belongsTo relationship
- Migration pattern established: `foreignId()->nullable()->constrained()->after()`

**Patterns Established:**
- All PHP files start with `declare(strict_types=1);`
- Type hints on all method signatures and return types
- PSR-12 code style via Laravel Pint

### Relevant Architecture Patterns and Constraints

From architecture.md lines 212-224:
```
MealAssignmentObserver Pattern:
- Triggers ONLY on model creation (not updates/deletions)
- Checks to_cook flag before processing
- Loads MealPlan → MealPlanDay → MealAssignment chain
- Queries affected shopping lists by meal_plan_id
```

**Observer Communication Pattern (architecture.md lines 432-445):**
```php
// MealAssignmentObserver@created
public function created(MealAssignment $meal): void
{
    // 1. Check if meal is for cooking
    if (!$meal->to_cook) {
        return;
    }

    // 2. Load relationships to prevent N+1 queries
    $meal->load('mealPlanDay.mealPlan', 'mealPlanRecipe.recipe.ingredients');

    // 3. Delegate to service
    app(ShoppingListSyncService::class)->syncNewMeal($meal);
}
```

**Observer Naming Convention (architecture.md lines 360-364):**
```php
// Singular model name + Observer
MealAssignmentObserver     // ✅ Phase 8 (first observer in project)
```

### Source Tree Components to Touch

**Files to CREATE:**
1. `app/Observers/MealAssignmentObserver.php` - First observer in project

**Files to MODIFY:**
1. `app/Providers/AppServiceProvider.php` - Register observer (Laravel 12 doesn't use EventServiceProvider)

### Testing Standards Summary

**Pest PHP Testing (from project-context.md lines 107-135):**
- Use `test()` function, NOT `@test` annotation
- Use factories for data creation
- Mock the ShoppingListSyncService to test observer in isolation
- Run `composer test` before marking complete

**Required Test Coverage:**
- Observer is registered and listens to MealAssignment::created
- Observer returns early when to_cook is false (no service call)
- Observer loads relationships when to_cook is true
- Observer calls ShoppingListSyncService with correct meal
- Observer does NOT trigger on MealAssignment::updated
- Observer does NOT trigger on MealAssignment::deleted

**Testing Pattern:**
```php
// Mock the service to test observer in isolation
Mockery::mock(ShoppingListSyncService::class)
    ->expects('syncNewMeal')
    ->once()
    ->with($meal);

// Create meal and verify observer fires
MealAssignment::factory()->create(['to_cook' => true]);
```

### Project Structure Notes

**Alignment with unified project structure:**
- This is the FIRST observer in the project - new `app/Observers/` directory
- Observer naming follows Laravel convention: `{Model}Observer`
- Observer registration via AppServiceProvider (Laravel 12 pattern)

**Detected conflicts or variances:** None identified - follows Laravel best practices.

### Dependencies

**Requires Story 1.1 to be complete:**
- ShoppingList model must have `mealPlan()` relationship available
- meal_plan_id column must exist in shopping_lists table

**Blocked by Story 1.3 (ShoppingListSyncService):**
- Observer invokes ShoppingListSyncService which doesn't exist yet
- Implement observer with service interface in mind - actual service will be created in Story 1.3
- Tests should mock the service interface

**Implementation Note:** The observer can be fully implemented and tested before Story 1.3 by mocking the ShoppingListSyncService interface.

### References

- **Observer Pattern Architecture** [Source: _bmad-output/planning-artifacts/architecture.md#Event System Design lines 212-224]
- **Observer Communication Pattern** [Source: _bmad-output/planning-artifacts/architecture.md#Communication Patterns lines 432-445]
- **Observer Naming Convention** [Source: _bmad-output/planning-artifacts/architecture.md#Naming Patterns lines 360-364]
- **Epic 1 Story 1.2 Requirements** [Source: _bmad-output/planning-artifacts/epics.md#Story 1.2 lines 162-189]
- **MealAssignment Model** [Source: app/Models/MealAssignment.php]
- **Project Context - PHP Rules** [Source: _bmad-output/project-context.md#Language-Specific Rules PHP lines 62-68]
- **Project Context - Testing** [Source: _bmad-output/project-context.md#Testing Rules lines 107-135]

## Dev Agent Record

### Agent Model Used

glm-4.7 (Claude Opus 4.6 equivalent)

### Debug Log References

No previous work in this epic - this is the second story of Phase 8.

### Completion Notes List

- **Implementation completed (2026-03-07):**
  - Created `app/Observers/MealAssignmentObserver.php` with `created()` method
  - Created `app/Services/ShoppingListSyncService.php` stub for Story 1.3
  - Created `tests/Feature/MealAssignmentObserverTest.php` with 5 tests covering:
    - Observer triggers sync when to_cook=true
    - Observer skips sync when to_cook=false
    - Observer doesn't trigger on update
    - Observer doesn't trigger on delete
    - Observer loads relationships before calling service
  - Registered observer in `app/Providers/AppServiceProvider.php` (Laravel 12 pattern)
  - All tests pass (544 PHP tests, 0 regressions)

### File List

- `app/Observers/MealAssignmentObserver.php` (new)
- `app/Services/ShoppingListSyncService.php` (new)
- `app/Providers/AppServiceProvider.php` (modified)
- `tests/Feature/MealAssignmentObserverTest.php` (new)
- `docs/BMAD-WORKFLOW-GUIDE.md` (modified)

## Change Log

- 2026-03-07: Implemented MealAssignmentObserver with addition-only synchronization pattern
- 2026-03-07: Code review fixes — test isolation via withoutObservers(), undefined variable fix in relationship closure, Mockery closure parameter renamed to $mealAssignment, Illuminate imports sorted alphabetically in AppServiceProvider
