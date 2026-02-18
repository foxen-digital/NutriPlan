# Meal Planning - Phase 4: "To Cook" Flags

## Overview
This phase introduces "to cook" flags to help users identify which specific meal instances need preparation within their meal plan.

## Core Functionality

### "To Cook" Flagging
- First meal assignment created from a MealPlanRecipe entry (i.e., when a recipe is added to a day for the first time) is automatically flagged as "to cook"
- Users can manually toggle the "to cook" flag for any meal assignment instance
- Visual indicators clearly distinguish meal assignments flagged for cooking on the calendar view
- Count of "to cook" items is displayed per day for quick reference

### Enhanced Meal Assignment Cards
- Meal assignment cards display the recipe name, serving count, and a toggle control for the "to cook" flag

## Database Structure

### MealAssignment Model Updates (from Phase 3)
- Add `to_cook` field (boolean)
  - Default value should be determined by the automatic flagging logic upon creation.
- Maintain existing relationships and `servings_count` field

## Implementation Details

### Backend Requirements
- Migration to add the `to_cook` boolean field to the `meal_assignments` table
- Logic within the meal assignment creation process to automatically flag the first created meal assignment instance from a `MealPlanRecipe` as `to_cook = true`
- API endpoint and method for toggling the `to_cook` status on an existing `MealAssignment`

### Frontend Requirements
- Update the meal assignment card component to include:
  - A visual indicator (e.g., icon, badge, border) when `to_cook` is true
  - A toggle control (e.g., checkbox, switch) to manually change the `to_cook` status
- Display the count of "to cook" items for each day on the calendar view

## Testing Criteria
- The first meal assignment created for a recipe on a specific day is automatically flagged as "to cook"
- Subsequent assignments of the same recipe to the same or different days are *not* automatically flagged
- Manual toggling of the "to cook" status works correctly and persists the change
- Visual indicators and counts for "to cook" items are displayed accurately on the frontend
- Database migration adds the `to_cook` field correctly

## Value Proposition
The "to cook" flagging system helps users track exactly which meal instances they need to prepare, adding practical value and clarity to the meal planning process, especially for larger or more complex meal plans. 
