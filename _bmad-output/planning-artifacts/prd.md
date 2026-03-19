---
stepsCompleted: ['step-01-init', 'step-02-discovery', 'step-02b-vision', 'step-02c-executive-summary', 'step-03-success', 'step-04-journeys', 'step-05-domain', 'step-06-innovation', 'step-07-project-type', 'step-08-scoping', 'step-09-functional', 'step-10-nonfunctional', 'step-11-polish', 'step-12-complete']
status: complete
completedDate: '2026-03-19'
inputDocuments:
  - '_bmad-output/project-context.md'
  - 'docs/index.md'
  - 'docs/project-overview.md'
  - 'docs/architecture.md'
  - 'docs/api-contracts.md'
  - 'docs/data-models.md'
  - 'docs/source-tree-analysis.md'
  - 'docs/component-inventory.md'
  - 'docs/development-guide.md'
  - 'docs/deployment-guide.md'
workflowType: 'prd'
briefCount: 0
researchCount: 0
projectDocsCount: 10
classification:
  projectType: web_app
  domain: general
  complexity: low
  projectContext: brownfield
---

# Product Requirements Document: Unit Conversion for Shopping Lists

**Project:** NutriPlan
**Author:** Mrdth
**Date:** 2026-03-19
**Type:** Brownfield feature addition — Web Application (Laravel 12 + Vue 3 + Inertia.js)

## Executive Summary

Unit Conversion for Shopping Lists extends NutriPlan's existing ingredient consolidation engine to handle mismatched units of measurement. When multiple recipes contribute the same ingredient using different units — e.g., one recipe using tablespoons and another using millilitres — the system converts and consolidates them into a single, unambiguous shopping list entry. The output unit is determined by the user's global imperial/metric preference. This is the first phase of a broader shopping list usability improvement, targeting the real-world mobile experience of using NutriPlan at the grocery store.

The intelligence is invisible. Users receive a clean, consolidated list without any indication that conversion occurred — the list simply reflects what they need to buy, in the units they expect. This extends an already-working consolidation foundation (same-unit matching) to handle the real-world messiness of recipe authoring where unit choices vary.

**Deliberate scope constraint:** Only same-dimension conversions (volume↔volume, weight↔weight) are supported in this phase. Cross-dimension conversions (volume↔weight) are explicitly deferred — if units cannot be reconciled, existing behaviour is preserved and items remain separate. The conversion engine is architected for future reuse in recipe display contexts.

## Success Criteria

### User Success

- A user at the supermarket sees a single, consolidated entry per ingredient — no duplicate lines for the same ingredient in compatible units
- Quantities are expressed in practical, purchasable amounts: volumes rounded **up** to the nearest 5ml, weights rounded **up** to the nearest 5g (ceiling, not nearest)
- The output unit matches the user's stored preference (metric by default)
- Items that cannot be consolidated (cross-dimension mismatches, unknown units) appear as separate entries without error — identical to current behaviour
- No previously-working consolidation (same-unit matching) regresses

### Business Success

- The feature ships as a self-contained, non-breaking addition
- Unit preference infrastructure (model setting, default metric) is in place and ready for a future UI surface without rework
- The conversion engine is structured for reuse in future contexts (recipe display)

### Technical Success

- A conversion table covers all standard units present in existing recipe data (tbsp, tsp, ml, l, fl oz, cup, g, kg, oz, lb as minimum)
- Conversion logic is encapsulated and independently testable, not embedded in shopping list generation
- Rounding applied as **ceiling** to nearest 5ml / 5g (metric) and nearest 5fl oz / 0.1oz (imperial)
- Conversion runs at **two trigger points**: (1) shopping list generation, (2) when a new "to cook" meal is added to a plan covered by an existing list (integrates with Phase 8 sync)
- Cross-dimension conversion attempts and unknown/non-standard units (e.g., "handful", "to taste", "pinch") pass through unchanged — no exceptions, no data loss
- Unit preference stored per-user via the existing Laravel model settings package, defaulting to metric

### Measurable Outcomes

- Zero duplicate entries for same-ingredient, same-dimension combinations after consolidation runs
- 100% of same-dimension unit pairs in the conversion table produce correct output (test-verified)
- No regression in existing same-unit consolidation (existing test suite passes)
- Unknown unit strings produce no errors and appear unchanged in the output list

## Product Scope

### MVP — Minimum Viable Product

- Same-dimension unit conversion engine (volume↔volume, weight↔weight) with full standard conversion table
- Integration with existing shopping list consolidation logic at generation time
- Integration with Phase 8 sync trigger (new "to cook" meal added to plan)
- Ceiling rounding to nearest 5ml / 5g (metric), nearest 5fl oz / 0.1oz (imperial)
- User unit preference stored against user model via existing settings package, defaulting to metric
- Silent fallback: unconvertible and unknown-unit pairs remain as separate line items

### Growth (Post-MVP)

- UI surface for users to view and change their imperial/metric preference
- Conversion table expansion based on real-world recipe data gaps (e.g., dl, additional fl oz variants)

### Vision (Future)

- Small-quantity suppression: omit trivial shopping amounts (e.g., "pinch of salt" → just "salt", or suppressed entirely)
- Cross-dimension conversion (volume↔weight) using ingredient-specific density data
- Unit conversion applied to recipe ingredient display

## User Journeys

### Journey 1: Sarah — The Sunday Planner (Primary, Happy Path)

Sarah plans her family's meals every Sunday using NutriPlan. She builds a week of dinners from her saved recipes — a French dressing that calls for `2 tbsp olive oil`, an Italian pasta that needs `60ml olive oil`, and a roast chicken that uses `1 tbsp olive oil`.

She taps "Generate Shopping List." In the past, she'd have seen three olive oil entries and done the mental arithmetic on the fly while standing in the supermarket aisle. Today, she sees `120ml olive oil` — one line, one number, one decision. She doesn't notice the conversion happened. She just notices the list is clean. At the supermarket she grabs a 250ml bottle — done.

**Requirements revealed:** Conversion engine at list generation time; correct output unit; ceiling rounding; single consolidated entry per same-dimension ingredient.

### Journey 2: Marcus — The Mid-Week Adaptor (Phase 8 Sync Integration)

Marcus generated his shopping list on Monday. On Wednesday evening he decides to add a new recipe — a curry that needs `200g chicken breast` and `½ cup coconut milk`. His list already has `300g chicken breast` from another meal.

He adds the meal. The next time he opens the list, the chicken entry reads `500g chicken breast`. The coconut milk appears as a new line — `120ml coconut milk` (converted from ½ cup, rounded up). He didn't regenerate anything. The list just updated.

**Requirements revealed:** Conversion runs on Phase 8 sync trigger; same logic as generation-time consolidation; new items added with converted units.

### Journey 3: Elena — The Recipe Importer (Edge Case, Unknown Units)

Elena imports recipes from websites using NutriPlan's AI import feature. Some older imported recipes have unusual units — `"a generous handful of spinach"`, `"1 pinch of salt"`, `"3 sprigs of thyme"`. She also has two recipes that both need flour — one in `cups`, one in `grams`.

She generates her shopping list. The flour entries remain separate — one `cups`, one `grams` — because the system can't reconcile volume and weight without density data. The spinach, salt, and thyme appear exactly as-is. Nothing breaks. Nothing disappears. The list is still useful.

**Requirements revealed:** Silent fallback for cross-dimension and unknown units; pass-through behaviour for non-standard unit strings; no errors surfaced to user.

### Journey 4: Developer — Extending the Conversion Table

A developer notices user-imported recipes use `"dl"` (decilitres), absent from the initial conversion table. They add the conversion factor to the central table. The encapsulated engine means this is a single-location change — no shopping list logic needs touching. They write a unit test for the new conversion pair and it passes.

**Requirements revealed:** Conversion table is centralised and independently extensible; logic is not embedded in list generation code; testable in isolation.

### Journey Requirements Summary

| Journey | Capabilities Required |
|---|---|
| Sarah (generation) | Conversion engine at list generation; ceiling rounding; unit preference output |
| Marcus (sync) | Conversion on Phase 8 sync trigger; same engine reused |
| Elena (edge cases) | Silent fallback for unknown/cross-dimension units |
| Developer | Encapsulated, independently testable conversion table |

## Technical Architecture

This feature adds a conversion layer to NutriPlan's existing shopping list pipeline. The feature is entirely backend — no new frontend components in MVP.

### Integration Points

The existing consolidation logic runs in two locations using the same `ingredient_id|unit` keying strategy:

1. **`ShoppingListService::generateFromMealPlan()`** — synchronous, at list creation time. The `$ingredients` array is built using `$key = $ingredient->id . '|' . ($unitValue ?? 'null')` (same-unit only). Conversion must be applied **after** accumulating all raw ingredients and **before** creating `ShoppingListItem` records, so cross-unit entries for the same ingredient resolve into a single item.

2. **`UpdateShoppingListJob::handle()`** — async (queued), triggered by Phase 8 sync. Currently keys existing items as `"{ingredient_id}:{unit}"` for lookup. Conversion must be applied here too, resolving the incoming ingredient's unit against existing list items before deciding to increment or create.

A dedicated `UnitConversionService` must be injected into both locations and must **not** be embedded in either the service or the job directly.

**`MeasurementUnit` enum already exists** (`App\Enums\MeasurementUnit`) and is used in both code paths. The conversion table operates on `MeasurementUnit` values, with graceful pass-through for `null` units or unrecognised string values.

### Data Flow

- **Source:** `ingredient_recipe` pivot table — original `amount` (float) and `unit` (`MeasurementUnit` enum or string)
- **Output:** `shopping_list_items` table — final consolidated/converted `quantity` and `unit`; original units not preserved on list items (by design)
- **User preference:** stored per user via the existing model settings package; read at conversion time to determine the target output unit dimension (metric/imperial)

### Implementation Constraints

- `UnitConversionService` must be independently injectable and testable with no dependency on shopping list models
- The conversion table maps `MeasurementUnit` enum values to base units: ml (metric volume), g (metric weight), fl oz (imperial volume), oz (imperial weight)
- Conversion + rounding runs as a pure function: input amount + input unit + output preference → output amount + output unit
- Both `ShoppingListService` and `UpdateShoppingListJob` call the same conversion logic to guarantee consistent behaviour across both triggers

## Project Scoping

### MVP Strategy

**Approach:** Problem-solving MVP — the feature has binary value: same-dimension duplicates are eliminated, or they aren't. No partial state is acceptable.

**Resource requirements:** Single developer. One sprint. One new service class, two integration point modifications, one user model setting.

**Contingency:** If time is constrained, the user preference setting can be hardcoded to metric for the first release. The conversion logic is the valuable deliverable.

### Risk Mitigation

**Technical:** `UpdateShoppingListJob` currently matches items by exact `ingredient_id:unit` key. After conversion, the incoming unit may differ from the stored unit. Resolution: convert the incoming ingredient to the user's preferred unit, then match against the existing item's unit. Low complexity — careful but straightforward logic adjustment.

**Market:** None — improvement to existing functionality for existing users, no adoption risk.

## Functional Requirements

### Unit Conversion Engine

- **FR1:** The system can convert an ingredient amount from one volume unit to another (e.g., tbsp → ml, cup → fl oz)
- **FR2:** The system can convert an ingredient amount from one weight unit to another (e.g., oz → g, lb → kg)
- **FR3:** The system can consolidate multiple amounts of the same ingredient across compatible same-dimension units into a single amount expressed in the user's preferred unit
- **FR4:** The system applies ceiling rounding to converted amounts (metric: nearest 5ml / nearest 5g; imperial: nearest 5fl oz / nearest 0.1oz)
- **FR5:** The conversion table is centralised and independently extensible without modifying shopping list or sync logic

### Shopping List Generation

- **FR6:** The system consolidates same-ingredient, same-dimension entries into a single shopping list item during list generation
- **FR7:** The system uses the generating user's stored unit preference to determine the output unit for consolidated items at generation time

### Shopping List Sync Integration

- **FR8:** The system applies unit conversion when a new "to cook" meal's ingredients are added to an existing shopping list via the sync trigger
- **FR8a:** The system uses the shopping list owner's stored unit preference to determine the output unit when adding synced ingredients to an existing shopping list
- **FR9:** The system increments an existing shopping list item's quantity when a synced ingredient converts to the same unit as the existing item for that ingredient
- **FR10:** The system creates a new shopping list item when a synced ingredient cannot be matched to any existing item for that ingredient after conversion

### Unit Preference Management

- **FR11:** Each user account has a stored unit preference (metric or imperial)
- **FR12:** The system defaults all user accounts to metric unit preference
- **FR13:** Users can view and update their unit preference *(Phase 2 — UI surface deferred)*

### Graceful Degradation

- **FR14:** The system preserves cross-dimension ingredient pairs (e.g., volume and weight entries for the same ingredient) as separate shopping list items without modification
- **FR15:** The system preserves ingredients with unrecognised or null units as separate shopping list items without modification
- **FR16:** The system produces no user-visible errors or warnings when unit conversion is not possible

## Non-Functional Requirements

### Performance

- Conversion of a typical shopping list (≤50 ingredients) completes in under 100ms on the synchronous generation path
- Conversion within `UpdateShoppingListJob` completes within the job's existing 5-second performance budget (per Phase 8 sync PRD)

### Reliability

- A failure or exception within the conversion engine must not prevent shopping list generation or a sync job from completing — pass-through fallback is guaranteed
- Existing shopping list data must not be modified or corrupted if conversion logic encounters unexpected input
- The conversion engine produces deterministic output — identical input always produces identical output regardless of call order or system state
