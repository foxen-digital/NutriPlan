# Project Specifications

This document provides an overview of all feature specifications for the NutriPlan project.

## Feature Specifications

| Feature | Description | Status | Specification |
|---------|-------------|---------|---------------|
| Core Recipe Management | Create, view, edit, and list recipes with CRUD functionality | ✅ | [View Spec](specs/core-recipe-management.md) |
| Recipe Import & Enhancement | Import recipes from external sites with automatic parsing | ✅ | [View Spec](specs/recipe-import.md) |
| Collections & Categories | Organize recipes with categories and custom collections | ✅ | [View Spec](specs/collections-categories.md) |
| Recipe Functionality | Recipe scaling, ingredient management, and optional units | ✅ | [View Spec](specs/recipe-functionality.md) |
| Favorite Recipes | Allow users to mark recipes as favorites and view their favorite recipes | ✅ | [View Spec](specs/favorite-recipes.md) |
| Recipe Deletion | Allow users to delete their own recipes | ✅ | [View Spec](specs/recipe-deletion.md) |
| My Recipes | Toggle to filter between all recipes and user's own recipes | ✅ | [View Spec](specs/my-recipes.md) |
| Recipe Visibility | Control recipe privacy and visibility with special handling for imported recipes | ✅ | [View Spec](specs/recipe-visibility.md) |
| User Recipe Filtering | Filter recipes by specific user, showing user profiles and replacing My Recipes toggle | ✅ | [View Spec](specs/user-recipe-filtering.md) |
| Ingredient Normalization Service | Create service to parse/normalize ingredients via LLM | ✅ | [View Spec](specs/feature-ingredient-normalization-service.md) |
| Integrate Ingredient Normalization | Integrate service, update schema & UI for normalized ingredients | ✅ | [View Spec](specs/feature-integrate-ingredient-normalization.md) |
| Meal Planning | Create and manage meal plans with recipes | 🚧 | [View Spec](specs/meal-planning.md) |

### Meal Planning Phases

| Phase | Description | Status | Specification |
|---------|-------------|---------|---------------|
| Phase 1: Basic Meal Plans | Create and manage empty meal plans with basic metadata | ✅ | [View Spec](specs/meal-planning-phase-1-basic-meal-plans.md) |
| Phase 2: Recipe Assignment | Add recipes to meal plans and manage servings | ✅ | [View Spec](specs/meal-planning-phase-2-recipe-assignment.md) |
| Phase 3a: Meal Tracking | Calculate and track available meals for recipes in a plan | ✅ | [View Spec](specs/meal-planning-phase-3a-meal-tracking.md) |
| Phase 3b: Day Structure | Create day-based organization for meal plans | ✅ | [View Spec](specs/meal-planning-phase-3b-day-structure.md) |
| Phase 3c: Meal Assignments | Assign recipe servings to specific days within the plan | ✅ | [View Spec](specs/meal-planning-phase-3c-meal-assignments.md) |
| Phase 4: "To Cook" Flags | Implement cooking flags for meal assignments | ✅ | [View Spec](specs/meal-planning-phase-4b-to-cook-flags.md) |
| Phase 5: Plan Copying | Enable copying existing plans to create new ones | ✅ | [View Spec](specs/meal-planning-phase-5-plan-copying.md) |
| Phase 6: Core Shopping List | Create/manage empty lists, add custom items, track purchase status | ✅ | [View Spec](specs/meal-planning-phase-7-core-shopping-list.md) |
| Phase 7: Shopping List Generation | Generate shopping lists automatically from meal plans | ✅ | [View Spec](specs/meal-planning-phase-7a-shopping-list-generation.md) |
| Phase 8: Shopping List Sync | Automatically update lists when meal plans change | ⏳ | [View Spec](specs/meal-planning-phase-7b-automatic-synchronization.md) |
| Phase 9: Shopping List Unit Conversion | Add unit conversion logic for ingredient consolidation | ⏳ | [View Spec](specs/meal-planning-phase-7c-enhancements.md) |
| Phase 10: Shopping List UI Enhancements | Add drag & drop reordering and filtering/sorting | ✅ | [View Spec](specs/meal-planning-phase-7d-shopping-list-ui-enhancements.md) |
| Phase 11: Barcode Scanning | Add items to shopping list via barcode scanning (Mobile) | ✅ | [View Spec](specs/meal-planning-phase-7x-barcode-scanning.md) |
| Phase 12: Nutritional Summaries | Show nutritional totals per day or week | 🔮 | [View Spec](specs/meal-planning-phase-8-nutritional-summaries.md) |
| Phase 13: Cooking Notifications | Reminders for meals flagged "to cook" | 🔮 | [View Spec](specs/meal-planning-phase-9-cooking-notifications.md) |
| Phase 14: Drag and Drop Interface | Implement drag-and-drop interface for meal assignments | ⏳ | [View Spec](specs/meal-planning-phase-4a-drag-and-drop.md) |
| Phase 15: Mobile Optimization | Fully optimize the experience for mobile devices | 🔮 | [View Spec](specs/meal-planning-phase-6-mobile-optimization.md) |

## Status Legend
- ✅ Complete
- 🚧 In Progress
- ⏳ Planned
- 🔮 Future Consideration 