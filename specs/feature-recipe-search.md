# Feature: Recipe Search

## 1. Overview

This feature allows users to search their recipes (and public recipes) by either the recipe's name/description or by its ingredients. The search functionality will be integrated into the existing recipe index page.

## 2. User Stories

- As a user, I want to be able to quickly find recipes by typing keywords related to their name or description.
- As a user, I want to be able to find recipes that contain specific ingredients.
- As a user, I want the search results to respect my current view (e.g., "My Recipes" or "All Recipes" or specific user's recipes).
- As a user, I want an intuitive interface to initiate and configure my search.

## 3. Requirements

### 3.1. Frontend UI

- **Trigger:** Add a dedicated search icon button next to the existing action buttons on the `Recipe Index` page (`resources/js/Pages/Recipes/Index.vue`).
    - This button should *not* be part of the floating action button group on mobile.
- **Search Modal:** Clicking the search icon opens a modal dialog.
    - **Input:** A text input field for the search term.
    - **Mode Selection:** Two radio buttons to select the search mode:
        - "Search name / description" (default)
        - "Search by ingredient"
    - **Controls:** "Cancel" and "Search" buttons.
        - "Cancel" closes the modal without changing the current view.
        - "Search" triggers the search based on the input and selected mode.
- **State Management:** The search term and mode should persist in the URL query parameters (`search_term`, `search_mode`) so the search state is bookmarkable and reflected on page reload/navigation.
- **Results Display:** The `Recipe Index` page should update to display only the recipes matching the search criteria, maintaining existing filtering (like "My Recipes" or user filtering). A visual indicator that a search filter is active might be useful (e.g., displaying the search term near the filters).

### 3.2. Backend Logic

- **Controller:** Modify the existing `App\Http\Controllers\RecipeController@index` method.
    - **Input Handling:** Accept optional `search_term` and `search_mode` query parameters from the request (`$request`).
    - **Filtering:**
        - If `search_term` is present:
            - Apply filtering *before* applying existing `show_mine` or `user_id` filters.
            - If `search_mode` is "name_description" (or similar key): Filter recipes where `name` or `description` contain the `search_term` (case-insensitive).
            - If `search_mode` is "ingredient": Filter recipes that have an associated `Ingredient` whose `name` (or normalized name if available later) contains the `search_term` (case-insensitive). This will require joining the `ingredients` table (or `recipe_ingredients` pivot table).
    - **Return Value:** Return the filtered and paginated recipes to the Inertia view as usual.

### 3.3. Routes

- No new routes are needed. The existing `recipes.index` route will be used, augmented with query parameters.

## 4. Implementation Plan

### 4.1. Backend

1.  **Update `RecipeController@index`:**
    - Pass `search_term` and `search_mode` from the `Request $request` to the `RecipeIndexService->getRecipes()` method. Update the `$filters` array passed to the service.
    - Update the Inertia response to include `search_term` and `search_mode` in the `filter` prop, so the frontend knows the current search state.
2.  **Update `RecipeIndexService`:**
    - Modify the `getRecipes` method signature to accept the search parameters within the `$filters` array (e.g., `$filters['search_term']`, `$filters['search_mode']`).
    - In the `applyFilters` method (or a new dedicated search filtering method called from `applyFilters`), add the logic to check for `search_term` and `search_mode`.
    - Modify the Eloquent query builder chain within the service:
        - Apply search filtering *before* other filters like category or user visibility.
        - Use a `when()` clause based on `isset($filters['search_term'])`.
        - Inside the `when()`, use another `when()` or `if/else` based on `$filters['search_mode'] ?? 'name_description'`.
        - For "name_description": Apply `where(function ($query) use ($searchTerm) { $query->where('name', 'ILIKE', "%{$searchTerm}%")->orWhere('description', 'ILIKE', "%{$searchTerm}%"); })`. (Use `ILIKE` for case-insensitivity in PostgreSQL).
        - For "ingredient": Apply `whereHas('ingredients', function ($query) use ($searchTerm) { $query->where('ingredients.name', 'ILIKE', "%{$searchTerm}%"); })`. Ensure the correct relationship and table/column name (`ingredients.name`) is used.
    - Ensure the pagination correctly appends the search query parameters. Laravel's default pagination usually handles `->appends(request()->query())` well, but verify this works as expected.

### 4.2. Frontend (`resources/js/Pages/Recipes/Index.vue`)

1.  **Add Search Icon Button:**
    - Place a suitable search icon button (e.g., from `lucide-vue-next`) in the header/action area.
    - Add `@click` handler to open the search modal.
2.  **Create Search Modal Component:** (`resources/js/Components/Recipes/RecipeSearchModal.vue` or similar)
    - Use a modal component (e.g., from `radix-vue` `Dialog`).
    - Include `Input`, `RadioGroup`, and `Button` components.
    - Manage internal state for `searchTerm` and `searchMode`.
    - On "Search" click:
        - Get the current `searchTerm` and `searchMode`.
        - Use Inertia's `router.get` method to navigate to the current route (`route('recipes.index')`), passing the search parameters along with any existing query parameters (like `show_mine` or `user_id`). Preserve scroll position.
        - Close the modal.
    - On "Cancel" click: Close the modal.
3.  **Integrate Modal:**
    - Import and register the modal component in `Index.vue`.
    - Use a `ref` (e.g., `showSearchModal`) to control its visibility.
    - Pass existing search parameters (from `page.props`) to the modal when opening it, so it remembers the last search.
4.  **Reflect Search State:**
    - Read `search_term` and `search_mode` from `page.props.filter` to potentially display an indicator that a search is active and to pre-fill the modal.
    - Ensure pagination links generated by Laravel automatically include the search parameters (verified in backend step).

## 5. Future Considerations

- Debounce search input to avoid excessive requests if triggering search dynamically.
- Consider more advanced search options (e.g., exact phrase, exclusion).
- Use a dedicated search engine (like Meilisearch or Algolia) for better performance and relevance on larger datasets, especially for ingredient searching.
- Allow searching by tags/categories. 