# Meal Planning - Phase 14: Drag and Drop Interface

## Overview
This phase enhances the meal planning interface by implementing a drag-and-drop system for adding recipes to days and organizing meal assignments.

## Core Functionality

### 1. Drag Recipe to Day
- **Action**: Drag a recipe from the available "Recipes" list onto a specific day card in the "Plan Days" grid.
- **Validation**: Before creating the assignment, check if the recipe has `servings_available` > 0 (based on its `pivot` data in the meal plan).
- **Result**: If valid, create a new `MealAssignment` for that recipe on that day.
- **Defaults**: The new assignment should default to using 1 serving. Users can edit this later.
- **Endpoint**: Utilize the existing `POST /meal-assignments` endpoint.
- **UI Feedback**: Visually indicate the drop action, update the day card with the new assignment, and potentially update the `servings_available` count on the recipe card.

### 2. Drag Assigned Meal Between Days
- **Action**: Drag an existing `MealAssignmentCard` from one day card to another day card.
- **Result**: Update the `meal_plan_day_id` associated with the dragged `MealAssignment`. Crucially, recalculate the `to_cook` flag for all assignments of this specific recipe within the meal plan to ensure only the chronologically first assignment is marked `to_cook = true`.
- **Endpoint**: Requires a **new** backend endpoint (e.g., `PATCH /meal-assignments/{id}/move`) to handle updating the day association and recalculating `to_cook` flags.
- **UI Feedback**: Visually show the meal assignment moving to the new day.

### 3. Reorder Assigned Meals within a Day
- **Action**: Drag an existing `MealAssignmentCard` to a different position within the *same* day card.
- **Result**: Update the display order of meal assignments for that specific day.
- **Endpoint**: Requires a **new** backend endpoint (e.g., `PATCH /meal-assignments/reorder`) that accepts the `meal_plan_day_id` and an ordered list of `meal_assignment_ids` for that day. This might necessitate adding an `order` column to the `meal_assignments` table.
- **UI Feedback**: Visually update the order of assignments within the day card.

## Implementation Plan

### Backend Requirements
1.  **Database**:
    *   Add an `order` (integer) column to the `meal_assignments` table to manage display order within a day. Assign based on creation time initially.
2.  **API Endpoints**:
    *   **Move Assignment**: Create a new route `PATCH /meal-assignments/{meal_assignment}/move` and corresponding controller action (`MealAssignmentMoveController@__invoke`). This action should accept a `new_meal_plan_day_id` in the request body and update the assignment's `meal_plan_day_id`. **After updating the day, it must query all assignments for the same recipe within the plan, order them by day, and update their `to_cook` flags so only the first chronological one is true.** Ensure authorization checks.
    *   **Reorder Assignments**: Create a new route `PATCH /meal-plan-days/{meal_plan_day}/reorder-assignments` and corresponding controller action (e.g., `MealPlanDayAssignmentOrderController@__invoke`). This action should accept an ordered array of `meal_assignment_ids` in the request body. It will iterate through the provided IDs and update the `order` column for each assignment belonging to that day. Ensure authorization checks.
    *   **Create Assignment**: Verify the existing `POST /meal-assignments` (`MealAssignmentController@store`) endpoint can correctly handle creating an assignment with a default serving count (e.g., 1) when initiated via drag-and-drop. It already accepts `meal_plan_day_id` and `meal_plan_recipe_id`.
3.  **Testing**:
    *   **Location**: Place new feature tests in `tests/Feature/Http/Controllers/`. (e.g., `MealAssignmentMoveControllerTest.php`, `MealPlanDayControllerReorderTest.php`).
    *   **Move Assignment Endpoint (`MMealAssignmentMoveController@__invoke`)**: 
        *   Test that an authenticated user can move an assignment they own to another valid day within the same meal plan.
        *   Test that the `meal_plan_day_id` is correctly updated.
        *   Test that the `to_cook` flag is correctly recalculated across all assignments for that recipe (e.g., the first assignment chronologically becomes true, others false).
        *   Test that an unauthenticated user cannot move an assignment.
        *   Test that a user cannot move an assignment they don't own.
        *   Test that an assignment cannot be moved to a day belonging to a different meal plan.
        *   Use `MealPlan`, `MealPlanDay`, `MealPlanRecipe`, `MealAssignment` factories for setup following AAA pattern.
    *   **Reorder Assignments Endpoint (`MealPlanDayAssignmentOrderController@__invoke`)**:
        *   Test that an authenticated user can reorder assignments within a day they own.
        *   Test that the `order` column is correctly updated for all affected assignments based on the provided ID list.
        *   Test that an unauthenticated user cannot reorder assignments.
        *   Test that a user cannot reorder assignments for a day they don't own.
        *   Test validation if an invalid `meal_assignment_id` is provided in the list.
        *   Use factories for setup following AAA pattern.
    *   **Existing Create Endpoint (`MealAssignmentController@store`)**: 
        *   Add a test case verifying that creating an assignment via drag-and-drop (simulated request) results in a default `to_cook` flag state (likely true if it's the first instance of that recipe, false otherwise) and a default `order` value.

### Frontend Requirements (`resources/js/pages/MealPlans/Show.vue`)
1.  **Dependency**: Ensure `vuedraggable-next` is installed (It is @package.json).
2.  **Recipe List Draggable**:
    *   Wrap the `RecipeCard` list (`v-for="recipe in mealPlan.recipes"`) with `<draggable>`.
    *   Configure `group` options: `{ name: 'recipes', pull: 'clone', put: false }`. This allows dragging *from* this list but not dropping *onto* it, and clones the item on drag start.
    *   Use `@end` event on this draggable to potentially reset any drag-specific visual state.
3.  **Day Card Draggables**:
    *   Inside the `v-for="day in daysWithDates"` loop, wrap the `MealAssignmentCard` list (`v-for="assignment in day.meal_assignments"`) with `<draggable>`.
    *   Configure `group` options: `{ name: 'meal-assignments', put: ['recipes', 'meal-assignments'] }`. This allows dropping items from the 'recipes' group and the 'meal-assignments' group (itself).
    *   Set `v-model` for the draggable instance to `day.meal_assignments` to automatically handle local array updates on drop/reorder.
    *   Implement event handlers:
        *   `@add(event)`: Triggered when an item (either a recipe clone or an assignment from another day) is dropped onto this day.
            *   If `event.from` was the recipe list draggable: Call the backend `store` endpoint using the recipe data (`event.item.__draggable_context.element`) and the target day's ID (`day.id`). Use a default serving count of 1. Check `servings_available` from the element data before calling the endpoint.
            *   If `event.from` was another day's draggable: This signifies a move *between* days. The `@remove` event on the source list and this `@add` event together confirm the move. Call the new `move` endpoint using the assignment ID (`event.item.__draggable_context.element.id`) and the target day's ID (`day.id`).
        *   `@update(event)`: Triggered when an item is reordered *within* the same day. Call the new `reorder` endpoint, passing the `day.id` and the updated `day.meal_assignments` array (or just their IDs) from the `v-model`.
        *   `@remove(event)`: Triggered when an item is dragged *out* of this day's list. Useful in conjunction with `@add` to detect moves between days.
4.  **Data Handling**:
    *   After successful backend calls (`store`, `move`, `reorder`), use `router.reload({ only: ['mealPlan'], preserveState: true, preserveScroll: true })` to refresh the `mealPlan` prop data without a full page reload.
    *   Ensure recipe data includes `pivot.servings_available`.
5.  **Visual Feedback**:
    *   Use CSS classes dynamically bound using draggable events (`@start`, `@end`, drag-over states) to provide visual cues (e.g., highlighting drop zones, changing cursor, styling the dragged item).
6.  **Mobile/Touch**:
    *   Verify `vuedraggable-next` handles touch events appropriately. Add `touchStartThreshold` or `delay` props if needed to avoid conflicts with scrolling. Consider larger drag handles visually for touch.
7.  **Accessibility**:
    *   Investigate `vuedraggable-next`'s accessibility features. May need supplementary ARIA attributes or alternative keyboard controls for users who cannot use drag-and-drop.

## Mobile Considerations
- Touch-based drag and drop implementation provided by `vuedraggable-next`.
- Larger visual drag handles might be needed on `MealAssignmentCard` and potentially `RecipeCard` for easier interaction on mobile.
- Consider using `delay` and `touchStartThreshold` props on `<draggable>` to prevent accidental drags during scrolling on touch devices.
- Haptic feedback during drag operations could be explored (browser/device support varies).

## Testing Criteria
- Dragging a recipe to a day creates a new assignment with 1 serving (if servings available).
- Dragging an assignment from Day A to Day B updates its `meal_plan_day_id` correctly in the backend and UI. **Verify that the `to_cook` flags are correctly recalculated across all assignments for that recipe.**
- Reordering assignments within Day A updates their `order`
