# Feature: Bulk Add Ingredients

**Version:** 1.0
**Status:** Planned
**Date:** 2026-10-14

## 1. Overview

Adding ingredients to a recipe one row at a time is slow, especially when copying an ingredient list from a website that is not supported by the URL importer. This feature adds a **"Bulk Add"** button to the recipe form's ingredients section. It opens a modal containing a textarea (one ingredient per line); on submit, the raw strings are sent to a new JSON endpoint that normalizes them via the existing `IngredientNormalizationService` and resolves each normalized ingredient to a real `Ingredient` row. The normalized rows are returned to the frontend and appended directly into the recipe form's ingredient repeater, where the user can review and adjust them before saving.

This is the **first synchronous, request-time use** of `IngredientNormalizationService` over HTTP (today it only runs inside the recipe import path, `RecipeParser`). The LLM call happens while the user waits, so the modal shows a loading state and the payload is capped.

## 2. Goals

-   Drastically reduce the effort of manually entering (or pasting) ingredient lists.
-   Reuse `IngredientNormalizationService` (LLM with regex fallback) rather than introducing a second parsing path.
-   Produce form rows that satisfy existing server-side validation (`ingredients.*.ingredient_id` must `exists:ingredients,id`), by resolving `base_name` to an `Ingredient` record server-side — the same `firstOrCreate` precedent used by `RecipeParser` (`app/Services/RecipeParser.php:175`).
-   Keep the modal flow self-contained and consistent with existing UI patterns (`ImportRecipeModal.vue`, `ComboboxWithCreate.vue`).

## 3. Requirements

### Functional

1. The ingredients section of the recipe form shows a **Bulk Add** button next to the existing **Add Ingredient** button (applies to both Create and Edit, since both share `RecipeForm.vue`).
2. Clicking it opens a modal with:
    - a multi-line textarea (one ingredient per line, e.g. `200 g plain flour`, `2 red onions, finely chopped`);
    - a short description explaining the expected format;
    - Cancel and Submit buttons, with a spinner on Submit while the request is in flight.
3. On submit, the trimmed, non-empty lines are POSTed to the new endpoint as a JSON array.
4. The endpoint normalizes the strings and returns form-ready ingredient rows; the modal closes and the rows are appended to the recipe form's ingredient list.
5. Any newly created `Ingredient` records are also added to the page's ingredient option list so the `ComboboxWithCreate` in each row displays the selection correctly.
6. If the request fails (validation or server error), the modal stays open, the user's text is preserved, and an error message is shown.
7. Rows land in the form in the same order as the pasted lines; the user can edit amount/unit/ingredient or delete rows afterwards, exactly like manually added rows.

### Constraints

-   **Max 50 lines** and **255 characters per line** — bounds LLM latency/cost; enforced server-side via a FormRequest (empty/whitespace lines are stripped client-side before sending).
-   The endpoint is authenticated (`auth:sanctum` + `verified` group, like the rest of `routes/web.php`).
-   Unit strings are passed through as returned by the normalization service (see §5.3 for the known mismatch with `config('recipe.measurement_units')`).
-   Duplicate lines are kept as duplicate rows (consistent with the import path, which attaches duplicates intentionally).

## 4. Technical Design

### 4.1 Backend

**Route** — `routes/web.php`, inside the authenticated group, under the existing `// Ingredients` comment (next to `ingredients.store` at line 45):

```php
Route::post('ingredients/normalize', NormalizeIngredientsController::class)->name('ingredients.normalize');
```

Rationale: this is an ingredient-domain helper, not tied to a recipe, so it sits alongside `Route::post('/ingredients', ...)` which already serves the ComboboxWithCreate create flow. (A `recipes/ingredients/normalize` variant would also fit the repo's recipe-action pattern; `ingredients.normalize` is preferred for symmetry with `ingredients.store`.)

**Form Request** — new `app/Http/Requests/Ingredient/NormalizeIngredientsRequest.php` (mirrors the `app/Http/Requests/Recipe/` subfolder convention):

```php
public function rules(): array
{
    return [
        'ingredients' => ['required', 'array', 'min:1', 'max:50'],
        'ingredients.*' => ['required', 'string', 'max:255'],
    ];
}
```

**Controller** — new single-action invokable `app/Http/Controllers/NormalizeIngredientsController.php` (matches the `RecipeImportController` / `FavoriteController` invokable convention):

```php
public function __invoke(
    NormalizeIngredientsRequest $request,
    IngredientNormalizationService $normalizationService
): JsonResponse {
    $ingredientStrings = array_values(array_filter(
        array_map(trim(...), $request->validated('ingredients')),
        fn (string $line): bool => $line !== '',
    ));

    $normalized = $normalizationService->normalize($ingredientStrings);

    $ingredients = [];
    foreach ($normalized as $data) {
        $baseName = trim((string) ($data['base_name'] ?? ''));

        if ($baseName === '') {
            continue; // Same skip rule as RecipeParser (lines 167-174)
        }

        $ingredient = Ingredient::firstOrCreate(['name' => $baseName]);

        $ingredients[] = [
            'ingredient_id' => $ingredient->id,
            'name' => $ingredient->name,
            'amount' => $data['quantity'],
            'unit' => $data['unit'],
            'description' => $data['description'],
            'original_string' => $data['original_string'],
        ];
    }

    return response()->json(['ingredients' => $ingredients]);
}
```

Key points:

-   The service (`app/Services/IngredientNormalizationService.php`) already guarantees each returned item has `base_name`, `quantity` (numeric or null), `unit`, `preparation_notes`, `description`, `original_string` — the controller only maps and resolves.
-   `Ingredient::firstOrCreate(['name' => $baseName])` mirrors `RecipeParser.php:175` exactly, so bulk-added rows and imported rows share the same ingredient dedup behaviour (no `is_common` override, matching existing precedent).
-   In the rare case the LLM returns an empty `base_name`, the row is skipped, so the response may contain fewer rows than inputs — the frontend appends whatever is returned.

**Response contract** — `200` with:

```json
{
    "ingredients": [
        {
            "ingredient_id": 42,
            "name": "red onion",
            "amount": 2,
            "unit": "piece",
            "description": "finely chopped",
            "original_string": "2 red onions, finely chopped"
        }
    ]
}
```

Errors are standard Laravel FormRequest validation responses (422) for oversize payloads; unhandled service failures are impossible in practice because the service falls back internally to the regex parser.

### 4.2 Frontend

**New component** — `resources/js/components/Recipe/BulkAddIngredientsModal.vue`, modelled on `resources/js/components/Recipe/ImportRecipeModal.vue`:

-   `<Dialog :open="open" @update:open="$emit('update:open', $event)">` with `DialogHeader` / `DialogTitle` ("Bulk Add Ingredients") / `DialogDescription` ("Paste one ingredient per line...").
-   A `Textarea` (`resources/js/components/ui/textarea`) with generous `rows`, `v-model` bound to a local ref (not Inertia `useForm` — this is a plain JSON call, matching `ComboboxWithCreate.vue:106-129`, which uses `axios.post` for its JSON endpoint).
-   `DialogFooter` with a ghost Cancel and a submit Button showing `Loader2Icon ... animate-spin` while `isSubmitting` (ImportRecipeModal pattern).
-   On submit: split on newlines → trim → filter empty → `axios.post(route('ingredients.normalize'), { ingredients: lines })`.
    -   Success: emit `added` with `response.data.ingredients`, clear the textarea, close the modal.
    -   Failure: keep modal open and text intact, render the error via `InputError` (message from a 422 response or a generic "Could not process ingredients" fallback), consistent with AddRecipeModal's error handling.

**RecipeForm changes** — `resources/js/components/Recipe/RecipeForm.vue`:

-   Import the modal and add a `showBulkAddModal` ref.
-   Next to the existing **Add Ingredient** button (line 121-124), add a **Bulk Add** outline button (icon: `ListPlusIcon` from `lucide-vue-next`).
-   Handler for the modal's `added` event:

```typescript
const handleBulkAdd = (rows: NormalizedIngredient[]) => {
    for (const row of rows) {
        form.ingredients.push({
            ingredient_id: row.ingredient_id,
            amount: row.amount ?? 1,
            unit: row.unit ?? '',
        });
    }

    const newOptions = rows
        .map((row) => ({ id: row.ingredient_id, name: row.name }))
        .filter((option) => !props.ingredients.some((i) => i.id === option.id));

    if (newOptions.length > 0) {
        emit('new-ingredients', newOptions);
    }
};
```

    (`amount ?? 1` / `unit ?? ''` matches the defaults used by `addIngredient()`, lines 253-259.)

-   Extend `defineEmits` with `(e: 'new-ingredients', ingredients: Array<{ id: number; name: string }>): void`.

**Page changes** — `resources/js/pages/Recipes/Create.vue` and `resources/js/pages/Recipes/Edit.vue` (both keep a local `ingredients = ref<Ingredient[]>([...props.ingredients])` so newly created options are selectable):

```typescript
const addNewIngredients = (newIngredients: Array<{ id: number; name: string }>) => {
    for (const ingredient of newIngredients) {
        ingredients.value.push(ingredient as Ingredient);
    }
};
```

    wired with `@new-ingredients="addNewIngredients"` alongside the existing `@new-ingredient="addNewIngredient"`.

### 4.3 Data flow summary

```
Textarea lines
  └─ POST ingredients.normalize { ingredients: string[] }
       └─ IngredientNormalizationService::normalize()   (LLM → fallback)
            └─ Ingredient::firstOrCreate(base_name)
                 └─ 200 { ingredients: [{ ingredient_id, name, amount, unit, ... }] }
                      └─ RecipeForm: form.ingredients.push({ ingredient_id, amount ?? 1, unit ?? '' })
                      └─ Page: ingredients option ref ← new { id, name } entries
```

## 5. Implementation Plan

**Branch:** create `feature/bulk-add-ingredients` from `main` (current working branch `feature/async-instruction-normalization` is unrelated).

1.  **(Backend)** `php artisan make:controller NormalizeIngredientsController --invokable --no-interaction` and `php artisan make:request Ingredient/NormalizeIngredientsRequest --no-interaction`; implement per §4.1; register the route.
2.  **(Backend)** Write the feature tests (§6) and run them: `vendor/bin/pest tests/Feature/Http/Controllers/NormalizeIngredientsControllerTest.php`.
3.  **(Frontend)** Create `BulkAddIngredientsModal.vue`; wire the Bulk Add button, `handleBulkAdd`, and the new `new-ingredients` emit into `RecipeForm.vue`; add the `addNewIngredients` handler to `Create.vue` and `Edit.vue`.
4.  **(Frontend)** Add `resources/js/components/Recipe/__tests__/BulkAddIngredientsModal.spec.ts` following `resources/js/components/MealPlan/Modals/__tests__/AddRecipeModal.spec.ts` (mock `axios` with `vi.mock('axios')`, stub global `route`); run `npm run test`.
5.  **(Verification)** `vendor/bin/pint --dirty --format agent`; `npm run build`; manually verify paste → normalize → edit rows → save on both Create and Edit pages.

## 6. Testing

**Feature tests** — `tests/Feature/Http/Controllers/NormalizeIngredientsControllerTest.php` (Pest, following `tests/Feature/Http/Controllers/RecipeControllerTest.php` conventions):

-   Guest is unauthenticated (redirect/401).
-   Validation: empty array, more than 50 items, non-string or empty item → 422.
-   Happy path: `IngredientNormalizationAgent::fake([...])` (pattern from `tests/Unit/Services/IngredientNormalizationServiceTest.php:31`) → assert 200, JSON shape, `Ingredient` rows created, and correct `amount`/`unit` mapping.
-   Idempotency: posting the same `base_name` twice does not create a duplicate `Ingredient` row (`firstOrCreate`).
-   Fallback path: `IngredientNormalizationAgent::fake(fn () => throw new \Exception('API request failed'))` → still 200, rows resolved via the regex fallback parser (deterministic — no network).

No new unit tests for `IngredientNormalizationService` are needed — behaviour is already covered by `tests/Unit/Services/IngredientNormalizationServiceTest.php`.

**Frontend tests** — `BulkAddIngredientsModal.spec.ts`:

-   Splits textarea content into trimmed, non-empty lines and posts the array to `mocked-route/ingredients.normalize`.
-   Emits `added` with the response payload and closes (`update:open` false) on success.
-   On API rejection: no `added`/close emitted, error message rendered, textarea content preserved.
-   Does not call the API when the textarea is empty/whitespace-only.

## 7. Future Considerations

-   **Async normalization**: for very large lists, the sync LLM call may take several seconds. If that becomes a pain point, follow the instruction-normalization precedent (`NormalizeRecipeInstructionsJob` + broadcast event + toast) — but note the result must then be merged into form state client-side, which is more complex than the current append-only flow.
-   **Unit mapping**: normalized unit strings (e.g. `pc` from the fallback `MeasurementUnit` enum, or free-text from the LLM) don't always match the `config('recipe.measurement_units')` values (`piece`), so the row's Unit select may render unmatched; the user fixes it manually today. A case-insensitive/alias mapping step in the controller would close this gap — and could then be back-ported to `RecipeParser` so imports benefit too.
-   **Review step in the modal**: show the normalized rows inside the modal for confirmation before appending, instead of appending directly to the form.
-   **Duplicate detection**: warn when a pasted line normalizes to an ingredient already present in the form.
