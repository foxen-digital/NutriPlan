# Feature: Integrate Ingredient Normalization Service

## Overview
This specification details the integration of the `IngredientNormalizationService` into the recipe import process. It covers the necessary database schema changes, model updates, modifications to the `RecipeParser` service, and updates to the user interface to display the normalized ingredient information.

## Depends On
- Core Recipe Management Functionality
- `App\Services\IngredientNormalizationService` (as defined in `specs/feature-ingredient-normalization-service.md`)
- Existing `App\Services\RecipeParser`
- Existing `App\Models\Recipe` and `App\Models\Ingredient`
- Existing Recipe Show view (`resources/js/pages/Recipes/Show.vue`)

## Leads To
- Recipes imported via URL will have normalized ingredient names and preparation details stored correctly.
- Recipe display will show more detailed ingredient information.

## Core Changes

### Database Schema Modification
- Remove the unused `description` column from the `ingredients` table.
- Add a `description` column (TEXT, nullable) to the `ingredient_recipe` pivot table to store preparation notes or the original ingredient string details.

### Model Updates
- Update the `Recipe` model's `ingredients` relationship to load the new `description` pivot column.

### Recipe Import Process (`RecipeParser`)
- Modify the `RecipeParser` service to call the `IngredientNormalizationService` instead of the old `IngredientParser` directly.
- Process the structured data returned by the normalization service to populate the `ingredients` relationship, including the pivot data (`amount`, `unit`, `description`).

### User Interface Update
- Modify the recipe display view (`Show.vue`) to utilize the `description` field from the ingredient pivot data instead of just the base ingredient name.

## Implementation Details

### Database Migrations

1.  **Drop Description Column from Ingredients:**
    - Create a migration to remove the `description` column from the `ingredients` table.
    ```bash
    php artisan make:migration remove_description_from_ingredients_table
    ```
    ```php
    // Inside the generated migration file's up() method:
    Schema::table('ingredients', function (Blueprint $table) {
        $table->dropColumn('description');
    });

    // Inside the down() method:
    Schema::table('ingredients', function (Blueprint $table) {
        $table->text('description')->nullable(); // Or match original definition
    });
    ```

2.  **Add Description Column to Pivot Table:**
    - Create a migration to add the `description` column to the `ingredient_recipe` table.
    ```bash
    php artisan make:migration add_description_to_ingredient_recipe_table
    ```
    ```php
    // Inside the generated migration file's up() method:
    Schema::table('ingredient_recipe', function (Blueprint $table) {
        $table->text('description')->nullable()->after('unit');
    });

    // Inside the down() method:
    Schema::table('ingredient_recipe', function (Blueprint $table) {
        $table->dropColumn('description');
    });
    ```
    *Note: Run `php artisan migrate` after creating these migrations. As the database will be cleared during development, no data backfill is needed.*

### Models

#### Recipe Model (`App\Models\Recipe`)
- Update the `ingredients()` relationship definition:
```php
<?php

namespace App\Models;

// ... other uses
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Recipe extends Model
{
    // ... existing model code ...

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
                    ->withPivot('amount', 'unit', 'description') // Ensure 'description' is included
                    ->withTimestamps();
    }

    // ... rest of model ...
}
```

### Services

#### RecipeParser (`App\Services\RecipeParser`)
- **Dependency Injection:** Inject `App\Services\IngredientNormalizationService` into the constructor.
- **Modify `parse()` method:**
    - Remove the loop that calls `ingredient_parser->parse()` individually for `recipeIngredient`.
    - Collect all raw ingredient strings (e.g., from `parse_recipeingredient` method) into an array (`$rawIngredientStrings`).
    - Call the normalization service: `$normalizedIngredients = $this->normalizationService->normalize($rawIngredientStrings);`
    - Initialize an empty array for pivot data: `$pivotData = [];`
    - Iterate through the `$normalizedIngredients` array returned by the service.
    - Inside the loop:
        - Skip if `base_name` is missing or empty in the normalized data.
        - Get or create the `Ingredient` model: `$ingredient = Ingredient::firstOrCreate(['name' => $normalizedData['base_name']]);`
        - Build the entry for the `$pivotData` array:
        ```php
        $pivotData[$ingredient->id] = [
            'amount' => $normalizedData['quantity'] ?? null,
            'unit' => $normalizedData['unit'] ?? null,
            // Use the combined 'description' or fallback to original
            'description' => $normalizedData['description'] ?? $normalizedData['original_string'] ?? $normalizedData['base_name'], // Ensure description is populated
        ];
        ```
    - After the loop, sync the data: `$recipe->ingredients()->sync($pivotData);`

### User Interface

#### Recipe Show View (`resources/js/pages/Recipes/Show.vue`)
- Locate the ingredient list rendering section (`v-for="ingredient in recipe.ingredients"`).
- Modify the displayed text to use the pivot description:
    - **Change:**
    ```vue
    <span class="font-medium">
        {{ formatScaledAmount(ingredient.pivot.amount) }}
        <template v-if="ingredient.pivot.unit">{{ ingredient.pivot.unit }}</template>
    </span>
    <span class="ml-1">{{ ingredient.name }}</span>
    ```
    - **To:**
    ```vue
    <span class="font-medium">
        {{ formatScaledAmount(ingredient.pivot.amount) }}
        <template v-if="ingredient.pivot.unit">{{ ingredient.pivot.unit }}</template>
    </span>
    <!-- Use the description from the pivot table -->
    <span class="ml-1">{{ ingredient.pivot.description }}</span>
    ```

## Testing Strategy

### Unit Tests
- No new unit tests specifically for this phase, as the core logic resides in the `IngredientNormalizationService` (tested separately) and the changes in `RecipeParser` are primarily integration.

### Feature Tests (`tests/Feature/...`)
- **Recipe Import Tests:**
    - Create or enhance tests for importing recipes via URL (e.g., `RecipeImportTest.php`).
    - **Mocking:** Mock the `IngredientNormalizationService` to return predictable structured data arrays (including successful cases, cases with nulls, and cases where fallback might have generated the data).
    - **Assertions:**
        - Assert that the `ingredients` table contains the correct *base* ingredient names.
        - Assert that the `ingredient_recipe` pivot table is populated correctly with `amount`, `unit`, AND the expected `description` based on the mocked service response.
        - Assert that the `Recipe` model, when loaded with `ingredients`, has the correct pivot data accessible.
- **Recipe Display Test:**
    - Create or enhance tests for the Recipe Show page/endpoint.
    - Seed a recipe with ingredients having specific data in the `ingredient_recipe.description` pivot field.
    - Make a request to the recipe show endpoint.
    - Assert that the rendered response (or Inertia props) contains the ingredient data with the `description` from the pivot table, not just the `ingredients.name`.

## Future Considerations
- **Recipe Add/Edit Forms:** As noted previously, the manual recipe creation/editing forms will eventually need updating to allow users to input detailed ingredient information (name + preparation) that populates the `ingredients.name` and `ingredient_recipe.description` fields correctly. This remains outside the scope of *this* integration phase.
 