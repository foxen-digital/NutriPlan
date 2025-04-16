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
| Instruction Normalization Service | Service for LLM-based instruction parsing | ✅ | [View Spec](specs/feature-instruction-normalization-service.md) |
| Parser Integration (Instructions) | Integrate instruction normalization into RecipeParser | ✅ | [View Spec](specs/feature-instruction-parser-integration.md) |
| Async Recipe Import | Move recipe import to a queued job | ✅ | [View Spec](specs/feature-async-recipe-import.md) |
| Real-time Notifications (Import) | WebSocket notifications for import status | ✅ | [View Spec](specs/feature-real-time-notifications.md) |
| Notification Toasts | Display notification messages to users using radix-vue toast components | ✅ | [View Spec](specs/notification-toasts.md) |
| Sanctum API Tokens | Backend & Frontend for managing Personal Access Tokens | ✅ | [View Spec](specs/feature-sanctum-api-tokens.md) |
| Recipe Import API | API endpoint for triggering imports via extensions/apps | ✅ | [View Spec](specs/feature-recipe-import-api.md) |
| Browser Extensions Core | Chrome/Firefox extensions for triggering recipe imports | ✅ | [View Spec](specs/feature-browser-extensions-core.md) |
| Web Share Target | Alternative to browser extensions for mobile, allowing mobiles users to 'share' a page directly to NutriPlan| 🔮 | [View Spec](specs/feature-web-share.md) |
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

## Future Ideas

This section outlines potential features for future consideration, inspired by user needs and competitor offerings.

### AI Recipe parsing
* For sites which do not use structured data for their recipes, we should build an AI based recipe parser to try and extract Ingredients and Instructions (nutrition info / categories if possible?)
* Use https://github.com/fivefilters/readability.php to extract the text content of teh page
* send this to an LLM, along with a detailed system message
* have the LLM return structured data matching what we already get for Ingredients & Instructions

### Recipe exports
* Allow users to export their recipe collection (manually created only?)
    - What format? json-ld? Custom

### Enhanced Nutrition & Dietary Features

*   **Automatic Nutrition Calculation:** Integrate with a nutrition API (e.g., USDA FoodData Central, Open Food Facts) to automatically calculate and display estimated nutritional information (calories, macros, etc.) for recipes.
    *   *Benefit:* Helps users track intake, meet dietary goals, and make informed choices.
*   **Advanced Dietary Filtering & Tagging:** Allow tagging recipes (user & community) with specific dietary needs (Gluten-Free, Vegan, Keto, etc.) and allergens. Enable filtering by these tags.
    *   *Benefit:* Increases utility for users with specific dietary requirements.
*   **Goal-Oriented Meal Planning:** Allow users to set dietary goals (calories, macros) and receive recipe/plan suggestions, potentially using AI for personalization.
    *   *Benefit:* Provides proactive support for health-focused users.

### Pantry & Inventory Management

*   **Digital Pantry:** Allow users to list ingredients they have in stock, optionally with quantities.
    *   *Benefit:* Reduces food waste and unnecessary purchases.
*   **Pantry Integration with Shopping List:** Cross-reference generated shopping lists with the pantry to remove or flag available items.
    *   *Benefit:* Creates smarter, more efficient shopping lists.
*   **"Use What You Have" Recipe Suggestions:** Suggest recipes based on selected pantry ingredients.
    *   *Benefit:* Inspires cooking with existing ingredients and minimizes waste.

### Improved Cooking Experience

*   **Dedicated Cooking Mode:** Create a view optimized for cooking (larger fonts, step highlighting, timers, screen awake).
    *   *Benefit:* Makes following recipes in the kitchen easier.
*   **Instructional Media:** Allow embedding step-by-step photos or short videos in recipe instructions.
    *   *Benefit:* Provides clearer guidance, especially for complex techniques.
*   **User Ratings & Reviews:** Implement a system for rating and reviewing community recipes.
    *   *Benefit:* Builds trust and helps users identify reliable recipes.
*   **Recipe Scaling Notes:** Allow notes on how instructions change when scaling (cooking times, pan sizes).
    *   *Benefit:* Improves success rate when scaling recipes.

### Deeper Community & Social Interaction

*   **User Profiles & Following:** Enhance profiles and allow users to follow others, creating a personalized feed.
    *   *Benefit:* Fosters community and personalized discovery.
*   **Recipe Recreations & Photos:** Allow users to upload photos of their results when making community recipes.
    *   *Benefit:* Increases engagement and provides real-world examples.
*   **Shared Collections/Meal Plans:** Enable collaborative recipe collections or meal plans.
    *   *Benefit:* Makes the tool more useful for families or groups.

### Advanced Planning & Discovery

*   **Meal Plan Templates:** Offer pre-built templates for various needs (e.g., "Budget Dinners," "Quick Weeknight Meals").
    *   *Benefit:* Provides starting points and inspiration.
*   **Leftover Integration:** Feature to easily incorporate planned leftovers into subsequent meals.
    *   *Benefit:* Streamlines planning for efficient food use.
*   **AI-Powered Recommendations:** Use user data for personalized recipe suggestions.
    *   *Benefit:* Improves discovery and engagement.
*   **Calendar Integration:** Allow exporting meal plans to external calendars (Google Calendar, iCal, etc.).
    *   *Benefit:* Integrates meal planning into users' broader schedules. 