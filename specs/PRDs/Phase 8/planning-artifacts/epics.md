---
stepsCompleted: [1, 2, 3, 4]
inputDocuments:
  - 'specs/PRDs/nutriplan-phase8-sync-prd.md'
  - '_bmad-output/planning-artifacts/architecture.md'
workflowType: 'epics-and-stories'
project_name: 'NutriPlan Phase 8'
user_name: 'Mrdth'
date: '2026-03-06'
lastStep: 4
status: 'complete'
completedAt: '2026-03-06'
---

# NutriPlan Phase 8 - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for NutriPlan Phase 8: Shopping List Synchronization, decomposing the requirements from the PRD and Architecture requirements into implementable stories.

## Requirements Inventory

### Functional Requirements

**FR1:** System must detect when a new meal is added to the meal plan (not updates or deletions)

**FR2:** System must check if the new meal falls within a shopping list's date range

**FR3:** System must check if the new meal is marked for cooking (to_cook flag)

**FR4:** System must identify all shopping lists whose date ranges overlap with the new meal

**FR5:** For ingredients already on the shopping list, system must increment quantities by the new meal's required amounts

**FR6:** For ingredients not on the shopping list, system must create new list items

**FR7:** System must process multiple overlapping shopping lists independently

**FR8:** System must process updates in the background without blocking user interactions

**FR9:** System must display a visual indicator (toast) when a shopping list is automatically updated

**FR10:** System must display explanatory text stating ingredients are never automatically removed

### NonFunctional Requirements

**NFR1:** Shopping list updates must complete within 5 seconds for typical user scenarios

**NFR2:** System must achieve 99% success rate measured over 30-day period

**NFR3:** Zero ingredients accidentally removed per 10,000 list updates

**NFR4:** 95th percentile of background list processing must complete within 2 seconds

**NFR5:** System must support asynchronous processing without blocking user interactions

**NFR6:** Addition-only constraint: ingredients are never automatically removed

### Additional Requirements

**Schema Addition Required:** Add `meal_plan_id` foreign key column to `shopping_lists` table (nullable, indexed)

**Model Relationship:** Add `mealPlan()` belongs-to relationship to ShoppingList model

**Observer Pattern:** Create `MealAssignmentObserver` to watch for meal creation events

**Service Layer:** Create `ShoppingListSyncService` for synchronization logic (separate from existing ShoppingListService)

**Background Job:** Create `UpdateShoppingListJob` with retry logic (3 attempts, exponential backoff)

**Event System:** Create `ShoppingListUpdated` event for toast notifications

**Query Optimization:** Use indexed `meal_plan_id` query for O(1) list lookup

**Error Handling:** Implement custom retry logic with structured logging

**Technology Constraints:** PHP 8.2+ with strict types, TypeScript strict mode, PSR-12 code style

### FR Coverage Map

| FR | Epic | Description |
|----|------|-------------|
| FR1 | Epic 1 | Detect new meal additions |
| FR2 | Epic 1 | Check date range overlap |
| FR3 | Epic 1 | Verify cooking flag |
| FR4 | Epic 1 | Identify affected shopping lists |
| FR5 | Epic 1 | Increment existing ingredient quantities |
| FR6 | Epic 1 | Create new list items |
| FR7 | Epic 1 | Handle multiple overlapping lists |
| FR8 | Epic 1 | Background processing |
| FR9 | Epic 2 | Visual indicator (toast notification) |
| FR10 | Epic 2 | Explanatory help text |

## Epic List

### Epic 1: Automatic Shopping List Synchronization

**Goal:** When users add new "to cook" meals to their plan, shopping lists automatically update to include the required ingredients.

**FRs covered:** FR1, FR2, FR3, FR4, FR5, FR6, FR7, FR8

**User Value Delivered:**
- Users can add meals to their plan at any time
- Shopping lists automatically include ingredients from new meals
- No manual updates required
- Multiple overlapping lists are handled automatically

**Implementation Notes:**
- Background processing ensures non-blocking UX
- Addition-only approach preserves user adjustments
- Uses indexed queries for performance

### Epic 2: User Feedback for Automatic Updates

**Goal:** Users are informed when their shopping lists are automatically updated and understand how the system behaves.

**FRs covered:** FR9, FR10

**User Value Delivered:**
- Users know when lists are updated via toast notifications
- Clear help text explains the addition-only behavior
- Builds trust through transparency

**Implementation Notes:**
- Leverages existing toast notification system
- Minimal UI changes required

---

## Epic 1: Automatic Shopping List Synchronization

When users add new "to cook" meals to their plan, shopping lists automatically update to include the required ingredients.

---

### Story 1.1: Database Schema Addition

As a system architect,
I want to add a `meal_plan_id` foreign key column to the shopping_lists table,
So that shopping lists can be efficiently queried by meal plan for synchronization.

**Acceptance Criteria:**

**Given** the shopping_lists table exists with the schema from Phase 7
**When** the migration runs
**Then** a `meal_plan_id` column is added as a foreignId (nullable, indexed)
**And** the foreign key constraint references the meal_plans table
**And** the column is placed after the `user_id` column

**Given** the ShoppingList model
**When** the model is updated
**Then** a `mealPlan()` belongsTo relationship is added
**And** the relationship is properly typed

**Given** existing shopping lists in the database
**When** the migration runs
**Then** existing records have `meal_plan_id` set to null
**And** no data is lost or modified

---

### Story 1.2: Meal Creation Observer

As a system,
I want to detect when new meals are added to a meal plan,
So that synchronization can be triggered for affected shopping lists.

**Acceptance Criteria:**

**Given** a MealAssignment model
**When** a new MealAssignment is created
**Then** the MealAssignmentObserver@created event is triggered
**And** the observer checks if the meal is marked for cooking (to_cook = true)

**Given** a meal marked as not for cooking (to_cook = false)
**When** the meal is created
**Then** the observer returns without triggering synchronization
**And** no jobs are dispatched

**Given** a meal marked for cooking (to_cook = true)
**When** the meal is created
**Then** the observer loads the MealPlanDay and MealPlan relationships
**And** the ShoppingListSyncService is invoked with the meal assignment

**Given** an existing meal is updated (not created)
**When** the meal is modified
**Then** the observer does not trigger synchronization
**And** no jobs are dispatched

---

### Story 1.3: Shopping List Sync Service

As a system,
I want a service that identifies affected shopping lists and dispatches update jobs,
So that list synchronization happens efficiently in the background.

**Acceptance Criteria:**

**Given** a new meal assignment marked for cooking
**When** the ShoppingListSyncService@syncNewMeal is invoked
**Then** the service retrieves the meal plan from the meal's relationships
**And** queries for all shopping lists with matching meal_plan_id

**Given** multiple shopping lists exist for the same meal plan
**When** the service finds affected lists
**Then** one UpdateShoppingListJob is dispatched for each shopping list
**And** jobs are dispatched with the shopping list and meal assignment

**Given** no shopping lists exist for the meal plan
**When** the service queries for lists
**Then** no jobs are dispatched
**And** the service completes without error

**Given** the service dispatches jobs
**When** logging occurs
**Then** the number of affected lists is logged with meal assignment context
**And** structured logging includes meal_plan_id and list count

---

### Story 1.4: Background List Update Job

As a system,
I want a background job that updates shopping lists with new meal ingredients,
So that synchronization happens asynchronously without blocking user interactions.

**Acceptance Criteria:**

**Given** a shopping list and a new meal assignment
**When** the UpdateShoppingListJob processes
**Then** the job loads the recipe and ingredients for the meal
**And** each ingredient is checked against existing list items

**Given** an ingredient already exists on the shopping list with the same unit
**When** the job processes the ingredient
**Then** the existing item's quantity is incremented by the meal's required amount
**And** the item's updated_at timestamp is refreshed

**Given** an ingredient does not exist on the shopping list
**When** the job processes the ingredient
**Then** a new ShoppingListItem is created with the ingredient details
**And** the item is ordered at the end of the list

**Given** the job completes successfully
**When** processing finishes
**Then** a ShoppingListUpdated event is dispatched with user ID and message
**And** the event includes the shopping list ID

**Given** the job encounters a recoverable error (database timeout, deadlock)
**When** the exception is caught
**Then** the job is retried with exponential backoff (5s, 15s, 30s)
**And** the error is logged with context

**Given** the job fails after 3 retry attempts
**When** the failed method is called
**Then** the failure is logged with shopping list ID, meal ID, and error message
**And** the job record is stored in failed_jobs table

**Given** the job processes ingredients
**When** relationships are loaded
**Then** the meal assignment's mealPlanRecipe and recipe relationships are eager loaded
**And** ingredients are loaded with the recipe to prevent N+1 queries

---

### Story 1.5: Synchronization Testing

As a development team,
I want comprehensive tests for the synchronization flow,
So that we can verify the feature works correctly and maintains quality standards.

**Acceptance Criteria:**

**Given** the ShoppingListSyncService
**When** unit tests are written
**Then** the service correctly identifies lists by meal_plan_id
**And** the service dispatches the correct number of jobs
**And** the service handles empty list results gracefully

**Given** the MealAssignmentObserver
**When** unit tests are written
**Then** the observer triggers on meal creation
**And** the observer skips meals not marked for cooking
**And** the observer skips meal updates (only creates trigger)

**Given** the UpdateShoppingListJob
**When** unit tests are written
**Then** existing ingredients have quantities incremented
**And** new ingredients create list items
**And** the job dispatches ShoppingListUpdated event on completion

**Given** the full synchronization flow
**When** integration tests are written
**Then** adding a new "to cook" meal updates all affected shopping lists
**And** adding a meal outside date ranges does not update lists
**And** adding a meal marked as leftover does not trigger updates
**And** multiple overlapping lists all receive updates independently

**Given** the addition-only constraint
**When** tests verify edge cases
**Then** updating an existing meal does not trigger synchronization
**And** deleting a meal does not remove ingredients from lists
**And** toggling cooking flag off does not remove ingredients

**Given** performance requirements (NFR1, NFR4)
**When** performance tests are run
**Then** synchronization completes within 5 seconds for typical scenarios
**And** 95th percentile processing is under 2 seconds

---

## Epic 2: User Feedback for Automatic Updates

Users are informed when their shopping lists are automatically updated and understand how the system behaves.

---

### Story 2.1: Toast Notification on List Update

As a user,
I want to see a notification when my shopping list is automatically updated,
So that I know when ingredients have been added to my list.

**Acceptance Criteria:**

**Given** a ShoppingListUpdated event is dispatched
**When** the event is broadcast to the user's private channel
**Then** the frontend receives the event with message and shopping list ID

**Given** the frontend receives a ShoppingListUpdated event
**When** the user is viewing any page in the application
**Then** a toast notification appears with the message "List updated with new ingredients from [meal name]"
**And** the toast is displayed for a minimum of 3 seconds
**And** the toast automatically dismisses after the duration

**Given** the toast notification is displayed
**When** the toast is visible
**Then** the notification uses the existing toast component styling
**And** the notification appears in the standard toast position

**Given** multiple ShoppingListUpdated events fire in quick succession
**When** multiple toasts are triggered
**Then** each toast is displayed independently
**And** toasts are stacked appropriately

---

### Story 2.2: Help Text for Addition-Only Behavior

As a user,
I want to understand how shopping list synchronization works,
So that I know what to expect when I add new meals to my plan.

**Acceptance Criteria:**

**Given** the shopping list detail page
**When** the help section is displayed
**Then** explanatory text states: "When you add new meals to your plan, ingredients are automatically added to existing shopping lists. Ingredients are never automatically removed."

**Given** the help text is displayed
**When** the text is rendered
**Then** the text is formatted for readability
**And** the text is positioned in a visible location on the shopping list page

**Given** a user viewing the shopping list
**When** the user reads the help text
**Then** the addition-only behavior is clearly communicated
**And** the user understands that manual adjustments are preserved
