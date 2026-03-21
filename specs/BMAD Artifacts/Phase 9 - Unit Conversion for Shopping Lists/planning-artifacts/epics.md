---
stepsCompleted: ['step-01-validate-prerequisites', 'step-02-design-epics', 'step-03-create-stories', 'step-04-final-validation']
status: 'complete'
completedAt: '2026-03-19'
inputDocuments:
  - '_bmad-output/planning-artifacts/prd.md'
  - '_bmad-output/planning-artifacts/architecture.md'
---

# NutriPlan - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for NutriPlan, decomposing the requirements from the PRD and Architecture requirements into implementable stories.

## Requirements Inventory

### Functional Requirements

FR1: The system can convert an ingredient amount from one volume unit to another (e.g., tbsp → ml, cup → fl oz)
FR2: The system can convert an ingredient amount from one weight unit to another (e.g., oz → g, lb → kg)
FR3: The system can consolidate multiple amounts of the same ingredient across compatible same-dimension units into a single amount expressed in the user's preferred unit
FR4: The system applies ceiling rounding to converted amounts (metric: nearest 5ml / nearest 5g; imperial: nearest 5fl oz / nearest 0.1oz)
FR5: The conversion table is centralised and independently extensible without modifying shopping list or sync logic
FR6: The system consolidates same-ingredient, same-dimension entries into a single shopping list item during list generation
FR7: The system uses the generating user's stored unit preference to determine the output unit for consolidated items at generation time
FR8: The system applies unit conversion when a new "to cook" meal's ingredients are added to an existing shopping list via the sync trigger
FR8a: The system uses the shopping list owner's stored unit preference to determine the output unit when adding synced ingredients to an existing shopping list
FR9: The system increments an existing shopping list item's quantity when a synced ingredient converts to the same unit as the existing item for that ingredient
FR10: The system creates a new shopping list item when a synced ingredient cannot be matched to any existing item for that ingredient after conversion
FR11: Each user account has a stored unit preference (metric or imperial)
FR12: The system defaults all user accounts to metric unit preference
FR13: Users can view and update their unit preference — **DEFERRED to Phase 2 (UI surface)**
FR14: The system preserves cross-dimension ingredient pairs (e.g., volume and weight entries for the same ingredient) as separate shopping list items without modification
FR15: The system preserves ingredients with unrecognised or null units as separate shopping list items without modification
FR16: The system produces no user-visible errors or warnings when unit conversion is not possible

### NonFunctional Requirements

NFR1: Conversion of a typical shopping list (≤50 ingredients) completes in under 100ms on the synchronous generation path
NFR2: Conversion within UpdateShoppingListJob completes within the job's existing 5-second performance budget (per Phase 8 sync PRD)
NFR3: A failure or exception within the conversion engine must not prevent shopping list generation or a sync job from completing — pass-through fallback is guaranteed
NFR4: Existing shopping list data must not be modified or corrupted if conversion logic encounters unexpected input
NFR5: The conversion engine produces deterministic output — identical input always produces identical output regardless of call order or system state

### Additional Requirements

- **Package install:** `composer require mrdth/laravel-model-settings` must be installed before user preference storage is possible
- **Database migration:** `php artisan make::msm User` generates a `settings` JSON column on the `users` table; must be run before User preference can be persisted
- **`MeasurementUnit` enum extension:** Existing `App\Enums\MeasurementUnit` must be extended with `OUNCE = 'oz'`, `POUND = 'lb'`, `FLUID_OUNCE = 'fl oz'` cases before the conversion service can be implemented; `isWeight()` and `isVolume()` must be updated
- **`UnitSystem` enum:** New `App\Enums\UnitSystem` (backed string enum: Metric/Imperial) must be created before service typing is possible
- **`HasSettings` trait:** Must be added to `App\Models\User` as part of package setup
- **`User::unitSystem()` method:** Encapsulates all preference resolution; callers (service, job) must never access the settings package directly
- **`UnitConversionService` isolation:** Must be a pure service with zero Eloquent model dependencies; independently unit-testable without database
- **Queued job injection pattern:** `UnitConversionService` must be injected via `UpdateShoppingListJob::handle()` method type-hint (NOT the constructor — constructor args are serialized to queue)
- **`ShoppingListService` injection:** Uses standard constructor injection for `UnitConversionService`
- **No frontend changes in MVP:** Zero Vue/Inertia/Tailwind changes required
- **No new routes:** Feature is entirely within existing service and job layer
- **FR13 deferred:** UI surface for viewing/changing unit preference is explicitly out of scope for this sprint

### FR Coverage Map

FR1: Epic 1 — Volume-to-volume conversion in UnitConversionService
FR2: Epic 1 — Weight-to-weight conversion in UnitConversionService
FR3: Epic 1 — Cross-unit consolidation in ShoppingListService
FR4: Epic 1 — Ceiling rounding in UnitConversionService
FR5: Epic 1 — Centralised extensible conversion table
FR6: Epic 1 — Consolidation at list generation time
FR7: Epic 1 — User preference at generation time
FR8: Epic 2 — Conversion applied at Phase 8 sync trigger
FR8a: Epic 2 — User preference at sync time
FR9: Epic 2 — Increment existing item when converted unit matches
FR10: Epic 2 — Create new item when no match after conversion
FR11: Epic 1 — User unit preference stored via model settings
FR12: Epic 1 — Default metric preference
FR13: Deferred — UI preference surface (Phase 2, out of scope)
FR14: Epic 1 — Cross-dimension pairs preserved as separate items
FR15: Epic 1 — Null/unknown unit items preserved as separate items
FR16: Epic 1 — No user-visible errors on conversion failure

## Epic List

### Epic 1: Clean Shopping List Generation
When a user generates a shopping list, compatible ingredient quantities from different recipes using different units are automatically consolidated into a single, correctly-rounded entry in the user's preferred unit system.
**FRs covered:** FR1, FR2, FR3, FR4, FR5, FR6, FR7, FR11, FR12, FR14, FR15, FR16

### Epic 2: Mid-Week Plan Sync with Unit Conversion
When a user adds a new "to cook" meal to their plan after generating a shopping list, the new ingredients are added to the existing list with the same unit consolidation logic — converted, rounded, and matched against existing items correctly.
**FRs covered:** FR8, FR8a, FR9, FR10
**Depends on:** Epic 1 (UnitConversionService and User::unitSystem() must exist)

## Epic 1: Clean Shopping List Generation

When a user generates a shopping list, compatible ingredient quantities from different recipes using different units are automatically consolidated into a single, correctly-rounded entry in the user's preferred unit system.

**FRs covered:** FR1, FR2, FR3, FR4, FR5, FR6, FR7, FR11, FR12, FR14, FR15, FR16

### Story 1.1: User Unit Preference Storage

As a NutriPlan user,
I want my account to have a unit system preference that defaults to metric,
So that shopping list generation can use the correct measurement system for me.

**Acceptance Criteria:**

**Given** the `mrdth/laravel-model-settings` package is installed, the settings migration has run, and the `HasSettings` trait is added to `User`
**When** `User::unitSystem()` is called on a user with no stored preference
**Then** it returns `UnitSystem::Metric`

**Given** a user has `'unit_system'` set to `'imperial'` via `setSetting()`
**When** `User::unitSystem()` is called
**Then** it returns `UnitSystem::Imperial`

**Given** `UNIT_SYSTEM_SETTING` is the constant on `UnitConversionService`
**When** the setting key is referenced anywhere in the codebase
**Then** it always uses `UnitConversionService::UNIT_SYSTEM_SETTING`, never a bare string literal

**Technical scope:** Install `mrdth/laravel-model-settings`, run `php artisan make::msm User` + migrate, create `App\Enums\UnitSystem`, add `HasSettings` trait to `User`, add `User::unitSystem()` method, unit tests.

*FRs addressed: FR11, FR12*

---

### Story 1.2: Unit Conversion Engine

As the NutriPlan application,
I want to convert ingredient amounts between compatible units of the same measurement dimension,
So that quantities measured in tablespoons, cups, grams, or ounces can all be compared and combined.

**Acceptance Criteria:**

**Given** a volume unit amount (e.g., `2` tablespoons)
**When** `convert(2, TABLESPOON, MILLILITER)` is called
**Then** it returns the correct ml equivalent (`30.0`)

**Given** a metric volume amount (e.g., `240ml`)
**When** `convert(240.0, MILLILITER, FLUID_OUNCE)` is called
**Then** it returns the correct fl oz equivalent (~`8.12`)

**Given** a weight unit amount (e.g., `1` ounce)
**When** `convert(1, OUNCE, GRAM)` is called
**Then** it returns the correct gram equivalent (~`28.35`)

**Given** a metric weight amount (e.g., `100g`)
**When** `convert(100.0, GRAM, OUNCE)` is called
**Then** it returns the correct oz equivalent (~`3.53`)

**Given** a cross-dimension pair (e.g., a volume unit and a weight unit)
**When** `convert()` is called
**Then** it returns `null` — no exception is thrown

**Given** a dimensionless unit (PIECE, PINCH, CLOVE) or null
**When** `convert()` is called
**Then** it returns `null` — no exception is thrown

**Given** a total metric volume amount (e.g., `112ml`)
**When** `applyCeilingRounding(112.0, MILLILITER, Metric)` is called
**Then** it returns `115` (ceiling to nearest 5ml)

**Given** a total metric weight amount (e.g., `103g`)
**When** `applyCeilingRounding(103.0, GRAM, Metric)` is called
**Then** it returns `105` (ceiling to nearest 5g)

**Given** a total imperial volume amount (e.g., `3.2 fl oz`)
**When** `applyCeilingRounding(3.2, FLUID_OUNCE, Imperial)` is called
**Then** it returns `5.0` (ceiling to nearest 5 fl oz)

**Given** a total imperial weight amount (e.g., `1.15 oz`)
**When** `applyCeilingRounding(1.15, OUNCE, Imperial)` is called
**Then** it returns `1.2` (ceiling to nearest 0.1 oz)

**Given** a volume source unit and `UnitSystem::Metric`
**When** `preferredUnit(TABLESPOON, Metric)` is called
**Then** it returns `MeasurementUnit::MILLILITER`

**Given** a volume source unit and `UnitSystem::Imperial`
**When** `preferredUnit(MILLILITER, Imperial)` is called
**Then** it returns `MeasurementUnit::FLUID_OUNCE`

**Given** a weight source unit and `UnitSystem::Metric`
**When** `preferredUnit(OUNCE, Metric)` is called
**Then** it returns `MeasurementUnit::GRAM`

**Given** a weight source unit and `UnitSystem::Imperial`
**When** `preferredUnit(GRAM, Imperial)` is called
**Then** it returns `MeasurementUnit::OUNCE`

**Given** a dimensionless source unit (PIECE, PINCH, CLOVE)
**When** `preferredUnit()` is called
**Then** it returns `null`

**And** the conversion table covers all standard units: tbsp, tsp, ml, l, fl oz, cup (volume) and g, kg, oz, lb (weight); `MeasurementUnit` enum includes OUNCE, POUND, FLUID_OUNCE cases

**Technical scope:** Extend `App\Enums\MeasurementUnit` (add OUNCE, POUND, FLUID_OUNCE; update `isWeight()`/`isVolume()`), create `App\Services\UnitConversionService` with conversion table, `convert()`, `applyCeilingRounding()`, `preferredUnit()` — unit tests only (no DB).

*FRs addressed: FR1, FR2, FR4, FR5, FR14, FR15, FR16*

---

### Story 1.3: Consolidated Shopping List Generation

As a NutriPlan user generating a shopping list,
I want ingredients that appear in multiple recipes with different compatible units to be consolidated into a single list entry,
So that my shopping list shows clean, unambiguous quantities in the units I use — with no mental arithmetic required at the supermarket.

**Acceptance Criteria:**

**Given** a meal plan where two recipes both require olive oil (one uses `2 tbsp`, another uses `60ml`) and the user has metric preference
**When** the shopping list is generated
**Then** a single `olive oil` entry appears with the combined quantity expressed in ml, with ceiling rounding applied to the total

**Given** a user with default metric preference and cross-unit ingredients
**When** the shopping list is generated
**Then** all consolidated quantities are expressed in metric units (ml for volume, g for weight)

**Given** a user with imperial preference and cross-unit ingredients (e.g., `2 tbsp` and `60ml` olive oil)
**When** the shopping list is generated
**Then** all consolidated quantities are expressed in imperial units (fl oz for volume, oz for weight)

**Given** two recipes that use the same ingredient in cross-dimension units (e.g., `1 cup flour` [volume] and `100g flour` [weight])
**When** the shopping list is generated
**Then** two separate flour entries appear on the list — neither is modified (pass-through)

**Given** an ingredient with a non-standard unit (e.g., `"handful"`, `"pinch"`, or null)
**When** the shopping list is generated
**Then** the item appears unchanged with its original unit — no error is produced

**Given** two recipes using the same ingredient in the same unit (existing behaviour)
**When** the shopping list is generated
**Then** they are still consolidated into one entry with their quantities summed (no regression)

**And** ceiling rounding is applied once to the final consolidated total — not to each individual intermediate conversion

**Technical scope:** Modify `App\Services\ShoppingListService` — inject `UnitConversionService` via constructor, add conversion pass between ingredient accumulation and `ShoppingListItem` creation. Feature tests.

*FRs addressed: FR3, FR6, FR7*

---

## Epic 2: Mid-Week Plan Sync with Unit Conversion

When a user adds a new "to cook" meal to their plan after generating a shopping list, the new ingredients are added to the existing list with the same unit consolidation logic — converted, rounded, and matched against existing items correctly.

**FRs covered:** FR8, FR8a, FR9, FR10
**Depends on:** Epic 1 (UnitConversionService and User::unitSystem() must exist)

### Story 2.1: Unit Conversion at Shopping List Sync

As a NutriPlan user who has already generated a shopping list,
I want newly added meals to automatically contribute their ingredients converted to my preferred unit system,
So that my shopping list stays accurate and consolidated even when I change my plan mid-week.

**Acceptance Criteria:**

**Given** an existing list item `300g chicken breast` and a new meal requires `200g chicken breast`
**When** the sync job runs
**Then** the existing item's quantity is incremented to `500g` (same unit, no conversion needed — existing behaviour preserved)

**Given** a metric-preference user with an existing list item `300g chicken breast` and a new meal requires `0.5 lb chicken breast`
**When** the sync job runs
**Then** the `0.5 lb` is converted to grams, ceiling-rounded, and added to the existing `300g` entry — a single consolidated entry remains

**Given** an imperial-preference user with an existing list item `10 fl oz olive oil` and a new meal requires `60ml olive oil`
**When** the sync job runs
**Then** the `60ml` is converted to fl oz, ceiling-rounded, and added to the existing `10 fl oz` entry

**Given** a new meal ingredient has an unconvertible unit (cross-dimension or non-standard)
**When** the sync job runs
**Then** a new separate list item is created using the ingredient's original unit — no error is produced and no existing item is modified

**Given** a new meal ingredient that doesn't exist on the list at all and the user has metric preference
**When** the sync job runs
**Then** a new list item is created with the ingredient amount converted to the user's preferred unit and ceiling-rounded

**Given** the shopping list belongs to User A but the meal was added by a session acting as User B
**When** the sync job runs
**Then** User A's unit preference is used for all conversion decisions — not the requesting session's preference

**And** ceiling rounding is applied to each incoming converted amount before it is added to an existing item or used to create a new item

**Technical scope:** Modify `App\Jobs\UpdateShoppingListJob` — inject `UnitConversionService` via `handle()` method type-hint (not constructor), convert incoming ingredient to preferred unit before lookup key is built, eager-load `$shoppingList->user` for preference resolution. Feature tests.

*FRs addressed: FR8, FR8a, FR9, FR10*
