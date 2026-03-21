---
stepsCompleted: ['step-01-init', 'step-02-context', 'step-03-starter', 'step-04-decisions', 'step-05-patterns', 'step-06-structure', 'step-07-validation', 'step-08-complete']
lastStep: 8
status: 'complete'
completedAt: '2026-03-19'
inputDocuments:
  - '_bmad-output/planning-artifacts/prd.md'
  - '_bmad-output/project-context.md'
  - 'docs/index.md'
  - 'docs/architecture.md'
  - 'docs/data-models.md'
  - 'docs/api-contracts.md'
  - 'docs/source-tree-analysis.md'
  - 'docs/development-guide.md'
workflowType: 'architecture'
project_name: 'NutriPlan'
user_name: 'Mrdth'
date: '2026-03-19'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis

### Requirements Overview

**Functional Requirements:**
16 FRs across 4 categories. Core engine (FR1–5): same-dimension unit conversion (volume↔volume, weight↔weight), centralized extensible conversion table, ceiling rounding. Generation integration (FR6–7): consolidate at list creation using user's stored unit preference. Sync integration (FR8–10, FR8a): identical conversion logic applied via queued job when new "to cook" meals are added. Preference management (FR11–12): per-user metric/imperial preference via model settings package, defaulting to metric; FR13 (UI surface) deferred to Phase 2.

Graceful degradation (FR14–16) is a first-class constraint: cross-dimension pairs, null units, and unrecognised strings all pass through unchanged — no exceptions, no user-visible errors.

**Non-Functional Requirements:**
- Performance: <100ms for ≤50 ingredients on synchronous generation path; within existing 5-second budget for UpdateShoppingListJob
- Reliability: conversion failures must not block list generation or sync; output is deterministic (identical input → identical output, call-order independent)

**Scale & Complexity:**
- Primary domain: Backend service layer (Laravel monolith)
- Complexity level: Low — 1 new service class, 2 integration point modifications, 1 user model setting, 1 database migration (settings column), 0 frontend changes
- Estimated architectural components: 3 (UnitConversionService, modified ShoppingListService, modified UpdateShoppingListJob)

### Technical Constraints & Dependencies

- `MeasurementUnit` enum (`App\Enums\MeasurementUnit`) already exists and is used at both integration points — conversion table must operate on its values
- `UnitConversionService` must have zero dependency on shopping list models — pure function contract: input amount + input unit + output preference → output amount + output unit
- Both trigger points must call identical conversion logic — no duplication
- Existing model settings package handles user preference persistence — no new storage infrastructure needed
- `app/ValueObjects/Measurement` already exists — evaluate for reuse or alignment
- Brownfield constraint: no breaking changes to existing consolidation behaviour (same-unit matching must continue to work identically)

### Cross-Cutting Concerns Identified

- **Unit preference access**: User preference must be read at both synchronous (request-time) and asynchronous (queued job) trigger points. The job must resolve the shopping list owner's preference, not the requesting user's.
- **Error isolation**: Conversion exceptions must be caught and suppressed within the service; fallback to pass-through must be guaranteed.
- **Idempotency**: Conversion output must be deterministic — same input produces same output regardless of call order or system state.
- **Rounding semantics**: Ceiling (not nearest) rounding to nearest 5ml/5g and nearest 5fl oz/0.1oz — non-obvious business rule encapsulated within the service, not at the call site.

## Starter Template Evaluation

### Primary Technology Domain

Backend service layer addition to existing Laravel 12 monolith. No greenfield bootstrapping required.

### Existing Technical Foundation (Brownfield)

This feature is added to an established stack. No starter template selection is needed — the existing codebase defines all technical decisions.

**Established Stack (no changes):**

- **Language & Runtime:** PHP 8.2+ / Laravel 12 / `declare(strict_types=1)` on every file
- **Architecture Pattern:** Service layer (`app/Services/`), Value objects (`app/ValueObjects/`), constructor property promotion, typed method signatures
- **Queue/Async:** Database-backed Laravel queue (`ShouldQueue` interface, `UpdateShoppingListJob`)
- **Testing Framework:** Pest 3 (feature + unit), 100% type coverage enforced, factories required
- **Code Quality:** PHPStan level 5, Laravel Pint (PSR-12), Rector
- **Frontend:** Not in scope for MVP — no Vue, Inertia, or Tailwind changes

**New Components to be Added:**

- `app/Services/UnitConversionService.php` — follows existing service conventions
- Modifications to `app/Services/ShoppingListService.php`
- Modifications to `app/Jobs/UpdateShoppingListJob.php`
- User unit preference setting via existing model settings package

## Core Architectural Decisions

### Decision Priority Analysis

**Critical Decisions (Block Implementation):**
- `MeasurementUnit` enum must be extended before conversion logic can be written
- `mrdth/laravel-model-settings` package must be installed before user preference can be stored
- `UnitSystem` enum must exist before `UnitConversionService` can be typed

**Important Decisions (Shape Architecture):**
- `UnitConversionService` contract (fine-grained methods, not higher-level consolidate)
- Base-unit conversion table approach (not direct pair mapping)
- User model `unitSystem()` dedicated method for preference resolution

**Deferred Decisions (Post-MVP):**
- UI surface for viewing/changing unit preference (FR13)
- Conversion table expansion (dl, additional fl oz variants)
- Future: cross-dimension conversion using ingredient density data

### Data Architecture

**One migration required.** The `mrdth/laravel-model-settings` package stores settings as JSON in a `settings` column on the model's table. The `users` table needs this column added.

**Package setup (complete sequence):**

```bash
# 1. Install package
composer require mrdth/laravel-model-settings

# 2. Publish config (optional — default column name 'settings' is fine)
php artisan vendor:publish --tag="laravel-model-settings-config"

# 3. Generate migration for the User model
php artisan make::msm User
# Creates a migration adding: $table->json('settings')->nullable();

# 4. Run migration
php artisan migrate
```

**Add `HasSettings` trait to `App\Models\User`:**

```php
use Mrdth\LaravelModelSettings\Concerns\HasSettings;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasSettings;
}
```

**Package API (verified from README):**
- `$model->getSetting($key, $default = null)` — retrieve a setting value or default
- `$model->setSetting($key, $value)` — create or update a setting
- `$model->hasSetting($key)` — check if setting exists
- `$model->deleteSetting($key)` — remove a setting

**User preference storage:**
- **Setting key:** `'unit_system'` (via `UnitConversionService::UNIT_SYSTEM_SETTING`)
- **Setting value:** `UnitSystem` enum string value (`'metric'` | `'imperial'`)
- **Default:** `'metric'` (resolved via `UnitSystem::Metric`)

`ShoppingListItem` table: no schema changes. Original units are not preserved on list items (by design per PRD).

### Authentication & Security

No changes. This feature operates entirely within existing auth-protected service/job boundaries. The shopping list owner's user is resolved from `$shoppingList->user_id` in the async job — no additional auth surface introduced.

### New Enums

**`App\Enums\UnitSystem`** (new):
```php
enum UnitSystem: string {
    case Metric = 'metric';
    case Imperial = 'imperial';
}
```

**`App\Enums\MeasurementUnit`** (extend existing):
Add three new cases:
- `case OUNCE = 'oz';` — imperial weight
- `case POUND = 'lb';` — imperial weight
- `case FLUID_OUNCE = 'fl oz';` — imperial volume

Extend `isWeight()` to include `OUNCE`, `POUND`.
Extend `isVolume()` to include `FLUID_OUNCE`.

### Service Contract: `UnitConversionService`

Pure service — zero dependency on shopping list models. Injectable via constructor.

**Public methods:**

```php
// Convert an amount from one unit to another.
// Returns null if cross-dimension, unknown unit, or conversion impossible (pass-through signal).
public function convert(float $amount, MeasurementUnit $from, MeasurementUnit $to): ?float

// Apply ceiling rounding per PRD spec.
// Metric: nearest 5ml (volume) / nearest 5g (weight)
// Imperial: nearest 5fl oz (volume) / nearest 0.1oz (weight)
public function applyCeilingRounding(float $amount, MeasurementUnit $unit, UnitSystem $system): float

// Return the preferred target unit for a given source unit's dimension + system.
// e.g., volume + metric → MeasurementUnit::MILLILITER
// Returns null if source unit has no clear dimension (e.g., PIECE, PINCH, CLOVE).
public function preferredUnit(MeasurementUnit $source, UnitSystem $system): ?MeasurementUnit
```

**Conversion table:** Private `const array` inside `UnitConversionService`. Base-unit approach:
- Each `MeasurementUnit` maps to a float factor relative to its dimension's base
- Metric volume base: `ml` (factor = 1.0); tsp = 5.0; tbsp = 15.0; cup = 240.0; l = 1000.0
- Metric weight base: `g` (factor = 1.0); kg = 1000.0
- Imperial volume base: `fl oz` (factor = 1.0); (metric units convert via ml↔fl oz bridge: 1 fl oz = 29.5735 ml)
- Imperial weight base: `oz` (factor = 1.0); lb = 16.0; (metric units convert via g↔oz bridge: 1 oz = 28.3495 g)
- Convert A→B: `amount × factor(A) / factor(B)`
- Cross-dimension attempts and null/unrecognised units return `null` (no exceptions)

### User Model: `unitSystem()` Method

`App\Models\User` requires:
1. The `HasSettings` trait from `mrdth/laravel-model-settings` (see Data Architecture setup steps)
2. A dedicated `unitSystem()` method:

```php
use Mrdth\LaravelModelSettings\Concerns\HasSettings;

// In the class body:
public function unitSystem(): UnitSystem
{
    return UnitSystem::from(
        $this->getSetting(UnitConversionService::UNIT_SYSTEM_SETTING, UnitSystem::Metric->value)
    );
}
```

This encapsulates preference resolution. Both `ShoppingListService` and `UpdateShoppingListJob` call `$user->unitSystem()` — neither knows about the settings package directly.

### Integration Patterns

**`ShoppingListService::generateFromMealPlan()`:**

Insert a conversion pass between ingredient accumulation (step 4) and item creation (step 5):
1. After the existing loop builds `$ingredients` (keyed `ingredient_id|unit`), group entries by `ingredient_id`
2. For each ingredient group with >1 unit variant: attempt cross-unit consolidation via `UnitConversionService`
3. Successfully consolidated groups: replace with single entry in preferred unit with ceiling rounding applied
4. Groups that cannot be consolidated (cross-dimension, null/unknown units): remain as separate entries unchanged
5. Proceed to step 5 with the resolved `$ingredients` array

The user's `UnitSystem` preference is resolved once at generation time: `$mealPlan->user->unitSystem()`.

**`UpdateShoppingListJob::handle()`:**

Modify the per-ingredient loop to convert before lookup:
1. For each incoming ingredient, resolve the list owner's preference: `$this->shoppingList->user->unitSystem()`
2. Attempt `convert($pivot->amount, $pivot->unit, $preferredUnit)` via `UnitConversionService`
3. If conversion succeeds: apply ceiling rounding; build lookup key using converted unit (`"{ingredient_id}:{convertedUnit}"`)
4. If conversion returns null (unknown/null unit or cross-dimension): fall back to original unit for lookup (existing behaviour preserved)
5. Increment existing item or create new item using the resolved amount and unit

Both trigger points load the user once and pass `UnitSystem` — no per-ingredient database queries.

### Decision Impact Analysis

**Implementation Sequence (dependency order):**
1. `composer require mrdth/laravel-model-settings`
2. Publish config: `php artisan vendor:publish --tag="laravel-model-settings-config"`
3. Generate migration: `php artisan make::msm User` → run `php artisan migrate`
4. Add `HasSettings` trait to `App\Models\User`
5. Add `UnitSystem` enum
6. Extend `MeasurementUnit` enum (add OUNCE, POUND, FLUID_OUNCE)
7. Add `unitSystem()` method to `User` model
8. Implement `UnitConversionService` with conversion table and all public methods
9. Integrate `UnitConversionService` into `ShoppingListService`
10. Integrate `UnitConversionService` into `UpdateShoppingListJob`
11. Tests at each layer

**Cross-Component Dependencies:**
- `UnitConversionService` depends on: `MeasurementUnit` enum, `UnitSystem` enum — nothing else
- `ShoppingListService` depends on: `UnitConversionService`, `User::unitSystem()`
- `UpdateShoppingListJob` depends on: `UnitConversionService`, `User::unitSystem()`
- `User::unitSystem()` depends on: `mrdth/laravel-model-settings`, `UnitSystem` enum

## Implementation Patterns & Consistency Rules

### Critical Conflict Points — Feature-Specific Rules

These rules address areas where an implementing AI agent could make a plausible but incorrect choice. General naming, structure, and formatting rules are in `_bmad-output/project-context.md`.

**8 conflict areas identified and resolved below.**

### Rounding Pattern

- Ceiling rounding is applied **once to the final consolidated total** — never to intermediate conversion results within a consolidation pass
- Applying rounding to each individual conversion before summing is an anti-pattern that compounds errors
- `applyCeilingRounding()` is called by the caller (service/job), not internally by `convert()`

### Pass-Through & Error Isolation Pattern

- `UnitConversionService::convert()` returns `null` for ALL impossible conversions: cross-dimension, unknown units, null units, unexpected exceptions
- Never throw from `UnitConversionService` — exceptions must be caught internally
- Never return `0.0` — this would silently destroy quantity data
- Never log individual conversion failures — this is expected, not exceptional
- Callers that receive `null` MUST preserve the original entry unchanged as a separate `ShoppingListItem` record — dropping items is forbidden

### User Preference Resolution Pattern

- `$user->unitSystem()` is called **once per list operation** before any ingredient loop
- The resolved `UnitSystem` value is a local variable passed into loops and helper calls
- Neither `ShoppingListService` nor `UpdateShoppingListJob` access the settings package directly — they call `$user->unitSystem()` only

### Settings Key Constant Pattern

- The settings key `'unit_system'` is defined as a public constant on `UnitConversionService`:
  `public const string UNIT_SYSTEM_SETTING = 'unit_system';`
- `User::unitSystem()` references `UnitConversionService::UNIT_SYSTEM_SETTING`, not a bare string literal
- No other string form of this key (`'unit_preference'`, `'units'`, etc.) is ever used

### Lookup Key Pattern (`UpdateShoppingListJob`)

- After successful conversion, the lookup key is built using the **converted unit's enum string value**: `"{$ingredient->id}:{$convertedUnit->value}"`
- This matches the format of existing stored items
- If conversion returns `null`, fall back to the original pivot unit string for the key (existing behaviour preserved exactly)

### `MeasurementUnit` Extension Pattern

- Imperial units are new **cases on the existing `App\Enums\MeasurementUnit` enum** — no new enum class, interface, or wrapper
- `isWeight()` and `isVolume()` are updated in the same enum file
- Case names follow existing SCREAMING_SNAKE pattern: `OUNCE`, `POUND`, `FLUID_OUNCE`
- String values: `'oz'`, `'lb'`, `'fl oz'`

### Test Classification Pattern

| Component | Test Type | Location |
|---|---|---|
| `UnitConversionService` (conversion, rounding, pass-through) | Unit | `tests/Unit/Services/UnitConversionServiceTest.php` |
| `ShoppingListService` integration | Feature | `tests/Feature/ShoppingList/` |
| `UpdateShoppingListJob` integration | Feature | `tests/Feature/Jobs/` |
| `User::unitSystem()` | Unit | `tests/Unit/Models/UserTest.php` |

`UnitConversionService` unit tests use **no database, no models, no factories** — pure input/output assertions only.

### All Agents MUST

- Return `null` (not throw, not return `0.0`) from `convert()` when conversion is impossible
- Apply ceiling rounding once at the end of accumulation, not per individual conversion
- Resolve `$user->unitSystem()` once per list operation, before ingredient loops
- Preserve unconsolidatable entries as separate `ShoppingListItem` records — never drop
- Reference the settings key via `UnitConversionService::UNIT_SYSTEM_SETTING`
- Extend `MeasurementUnit` (not create a new enum) for imperial unit cases
- Write `UnitConversionService` tests as unit tests, not feature tests

### Anti-Patterns to Avoid

```php
// ❌ Rounding per conversion (compounding errors)
$converted = $service->convert($amount, $from, $to);
$rounded = $service->applyCeilingRounding($converted, $to, $system); // DON'T round here
$total += $rounded;

// ✅ Correct: accumulate raw converted values, round once at the end
$total += $service->convert($amount, $from, $to);
$rounded = $service->applyCeilingRounding($total, $targetUnit, $system);

// ❌ Silently dropping unconvertible items (forbidden)
if ($converted === null) { continue; }

// ✅ Correct: preserve as separate entry
if ($converted === null) { $unconvertible[] = $originalEntry; }

// ❌ Calling unitSystem() inside loop (N queries)
foreach ($ingredients as $ingredient) {
    $system = $user->unitSystem();
}

// ✅ Correct: resolve once before loop
$system = $user->unitSystem();
foreach ($ingredients as $ingredient) { /* use $system */ }
```

## Project Structure & Boundaries

### Complete Project Delta — Files Created & Modified

This is a brownfield addition. Only files changed or created by this feature are listed, with existing reference files shown for boundary context.

#### New Files

```
app/
├── Enums/
│   └── UnitSystem.php                          [NEW] metric/imperial enum
├── Services/
│   └── UnitConversionService.php               [NEW] core conversion engine

tests/
├── Unit/
│   ├── Models/
│   │   └── UserTest.php                        [NEW or MODIFIED] unitSystem() method tests
│   └── Services/
│       └── UnitConversionServiceTest.php       [NEW] pure unit tests — no DB
```

#### Modified Files

```
app/
├── Enums/
│   └── MeasurementUnit.php                     [MODIFIED] add OUNCE, POUND, FLUID_OUNCE cases;
│                                                           update isWeight(), isVolume()
├── Models/
│   └── User.php                                [MODIFIED] add HasSettings trait;
│                                                           add unitSystem(): UnitSystem method
├── Services/
│   └── ShoppingListService.php                 [MODIFIED] inject UnitConversionService;
│                                                           add conversion pass after accumulation
├── Jobs/
│   └── UpdateShoppingListJob.php               [MODIFIED] handle() injection of UnitConversionService;
│                                                           convert before lookup

database/migrations/
│   └── xxxx_add_settings_to_users_table.php    [NEW via artisan] adds settings JSON column
│                                                           generated by: php artisan make::msm User

tests/
├── Feature/
│   ├── ShoppingList/
│   │   └── ShoppingListGenerationTest.php      [MODIFIED] add cross-unit consolidation cases
│   └── Jobs/
│       └── UpdateShoppingListJobTest.php        [MODIFIED] add unit conversion integration cases

composer.json                                   [MODIFIED] add mrdth/laravel-model-settings
```

#### No Changes

```
resources/js/                                   no frontend changes
routes/                                         no new routes
app/ValueObjects/Measurement.php                reused as return type; no modification needed
```

### Requirements to Structure Mapping

| FR | Component(s) |
|---|---|
| FR1 — volume unit conversion | `UnitConversionService::convert()` + conversion table |
| FR2 — weight unit conversion | `UnitConversionService::convert()` + conversion table |
| FR3 — cross-unit consolidation | `ShoppingListService` (conversion pass) + `UnitConversionService` |
| FR4 — ceiling rounding | `UnitConversionService::applyCeilingRounding()` |
| FR5 — centralized extensible table | Conversion table const in `UnitConversionService` |
| FR6 — consolidation at generation | `ShoppingListService::generateFromMealPlan()` |
| FR7 — user preference at generation | `User::unitSystem()` called in `ShoppingListService` |
| FR8 — conversion at sync trigger | `UpdateShoppingListJob::handle()` |
| FR8a — user preference at sync | `User::unitSystem()` called in `UpdateShoppingListJob` |
| FR9 — increment existing on sync | `UpdateShoppingListJob::handle()` (existing logic, adjusted key) |
| FR10 — create new item on sync | `UpdateShoppingListJob::handle()` (existing logic, adjusted key) |
| FR11 — user unit preference stored | `User::unitSystem()` + `mrdth/laravel-model-settings` |
| FR12 — default metric | `User::unitSystem()` default fallback = `UnitSystem::Metric` |
| FR13 — UI preference surface | Deferred — no files |
| FR14 — cross-dimension pass-through | `UnitConversionService::convert()` returns null; callers preserve |
| FR15 — null/unknown unit pass-through | `UnitConversionService::convert()` returns null; callers preserve |
| FR16 — no user-visible errors | `UnitConversionService` swallows all exceptions internally |

### Architectural Boundaries

**`UnitConversionService` boundary (strict):**
- Inputs: `float $amount`, `MeasurementUnit` enum values, `UnitSystem` enum values
- Outputs: `?float`, `float`, `?MeasurementUnit` — primitive types and enums only
- Zero knowledge of: `ShoppingList`, `ShoppingListItem`, `User`, `MealPlan`, any Eloquent model
- Zero database access — pure computation only

**`ShoppingListService` boundary:**
- Resolves user preference once: `$mealPlan->user->unitSystem()`
- Calls `UnitConversionService` for conversion; owns the consolidation loop logic
- Creates `ShoppingListItem` records — this is where converted data lands

**`UpdateShoppingListJob` boundary:**
- Resolves user preference once: `$this->shoppingList->user->unitSystem()` (requires user eager-loaded)
- Calls `UnitConversionService` per ingredient; owns increment/create decision

**`User::unitSystem()` boundary:**
- Returns `UnitSystem` enum — callers never see the settings package
- Encapsulates default fallback (`UnitSystem::Metric`) in one place

### Data Flow

```
Shopping List Generation:
MealPlan → ShoppingListService
  → $user->unitSystem() [once]
  → Loop: collect raw ingredients (existing logic, keyed by ingredient_id|unit)
  → Conversion pass: group by ingredient_id
      → for each multi-unit group: UnitConversionService::convert() + applyCeilingRounding()
      → unconvertible entries: preserved as-is
  → ShoppingListItem::create() for each resolved entry

Shopping List Sync (Phase 8):
MealAssignment → UpdateShoppingListJob (queued)
  → $this->shoppingList->user->unitSystem() [once]
  → Loop: for each ingredient in recipe
      → UnitConversionService::preferredUnit() → target MeasurementUnit
      → UnitConversionService::convert() → ?float
      → if converted: applyCeilingRounding() → build key "{id}:{convertedUnit->value}"
      → if not converted: build key "{id}:{original_unit}" (existing fallback)
      → match key against $existingItems → increment or create
  → ShoppingListUpdated::dispatch()

User Preference:
$user->getSetting(UnitConversionService::UNIT_SYSTEM_SETTING, UnitSystem::Metric->value)
  → UnitSystem::from(result) → UnitSystem enum
```

### Integration Points

**Internal — constructor injection:**
- `ShoppingListService` receives `UnitConversionService` via constructor promotion
- `UpdateShoppingListJob` receives `UnitConversionService` via constructor promotion
- Laravel's service container auto-resolves both (no manual binding required)

**External — `mrdth/laravel-model-settings` package:**
- Accessed only through `User::unitSystem()` — no direct package calls elsewhere
- Full setup sequence documented in Data Architecture section (install, publish, migration, trait)

### Test Coverage Map

| Test File | Tests |
|---|---|
| `UnitConversionServiceTest.php` | convert() for each unit pair, ceiling rounding values, null for cross-dimension, null for null input, null for PIECE/PINCH/CLOVE, preferredUnit() for all combinations |
| `UserTest.php` | unitSystem() returns Metric by default, returns Imperial when set |
| `ShoppingListGenerationTest.php` | same-unit consolidation (regression), cross-unit consolidation, cross-dimension items remain separate, unknown unit items remain separate |
| `UpdateShoppingListJobTest.php` | converted unit matches existing item (increment), converted unit creates new item, conversion impossible (falls back to original key) |

## Architecture Validation Results

### Coherence Validation — ✅ Pass (with correction)

All decisions are internally consistent and technology-compatible. One critical correction applied during validation:

**Job Service Injection Correction:**
`UnitConversionService` must be injected via `UpdateShoppingListJob::handle()` type-hint, NOT the constructor. Laravel serializes constructor arguments of queued jobs — services are not serializable and will cause queue failures.

```php
// ✅ Correct
public function handle(UnitConversionService $conversionService): void { ... }

// ❌ Forbidden in queued jobs
public function __construct(..., UnitConversionService $conversionService) {}
```

`ShoppingListService` (a regular service, not a job) uses standard constructor injection.

### Requirements Coverage Validation — ✅ Pass

All 16 FRs and FR8a are mapped to specific implementation files (see Requirements to Structure Mapping in step 6). FR13 (UI preference surface) is explicitly deferred with no architectural gap — `User::unitSystem()` makes the surface trivially addable later.

NFRs covered:
- Performance: Pure arithmetic O(n≤50) on sync path; no database in conversion hot path
- Reliability: null contract guarantees no exception propagation; deterministic pure functions
- No data loss: pass-through preservation enforced by pattern rules

### Implementation Readiness Validation — ✅ Pass

All gaps from initial validation have been resolved.

### Gap Analysis Results

| Priority | Gap | Status |
|---|---|---|
| Critical | Job service injection via constructor (serialization) | ✅ Resolved — use handle() injection |
| Important | `mrdth/laravel-model-settings` API unverified | ✅ Resolved — README verified; full setup steps documented in Data Architecture |
| Important | Migration required for settings column | ✅ Resolved — `php artisan make::msm User` generates it; documented in Data Architecture |
| Minor | `ShoppingListService` gains first constructor | ✅ Non-issue — standard Laravel pattern |

### Architecture Completeness Checklist

**✅ Requirements Analysis**
- [x] Project context thoroughly analyzed
- [x] Scale and complexity assessed (Low — brownfield, 1 sprint)
- [x] Technical constraints identified (existing enums, missing imperial units, package install + migration)
- [x] Cross-cutting concerns mapped (preference resolution, error isolation, idempotency)

**✅ Architectural Decisions**
- [x] Critical decisions documented (`UnitSystem` enum, enum extension, service contract, table structure)
- [x] Technology additions specified (`mrdth/laravel-model-settings`)
- [x] Integration patterns defined (generation + sync trigger)
- [x] Performance considerations addressed (O(n), single preference read)

**✅ Implementation Patterns**
- [x] 8 feature-specific conflict points identified and resolved
- [x] Rounding pattern specified (once, after accumulation)
- [x] Error isolation pattern specified (null return, no throw, no drop)
- [x] Anti-patterns documented with code examples

**✅ Project Structure**
- [x] Complete file delta defined (new/modified/unchanged)
- [x] All 16 FRs mapped to specific files
- [x] Data flow documented for both trigger points
- [x] Test classification and locations specified

### Architecture Readiness Assessment

**Overall Status: READY FOR IMPLEMENTATION**

**Confidence Level: High**

**Key Strengths:**
- Minimal footprint: 2 new files, 5 modified files, 1 migration (settings column), 0 frontend changes
- Strong isolation: `UnitConversionService` has zero model dependencies — fully unit-testable
- Brownfield-safe: existing same-unit consolidation untouched; pass-through guarantees no regression
- Clear implementation sequence with dependency order documented

**Areas for Future Enhancement (Post-MVP):**
- FR13: UI surface for viewing/changing unit preference
- Conversion table expansion (dl, additional variants)
- Cross-dimension conversion using ingredient density data

### Implementation Handoff

**AI Agent Guidelines:**
- Follow all architectural decisions exactly as documented
- Use `handle()` method injection for services in `UpdateShoppingListJob` (not constructor)
- Package setup and API are fully documented in the Data Architecture section — no verification needed
- Write `UnitConversionService` tests as unit tests — no database, no factories
- Preserve unconsolidatable entries — never drop items

**Implementation Sequence:**
1. `composer require mrdth/laravel-model-settings`
2. Generate and run migration: `php artisan make::msm User` → `php artisan migrate`
3. Add `HasSettings` trait to `App\Models\User`
4. Create `App\Enums\UnitSystem`
5. Extend `App\Enums\MeasurementUnit` (add OUNCE, POUND, FLUID_OUNCE)
6. Add `User::unitSystem()` method
7. Implement `UnitConversionService` with conversion table, `convert()`, `applyCeilingRounding()`, `preferredUnit()`
8. Modify `ShoppingListService` — inject service, add conversion pass
9. Modify `UpdateShoppingListJob` — `handle()` injection, convert before lookup
10. Unit tests for `UnitConversionService` and `User::unitSystem()`
11. Feature tests for `ShoppingListService` and `UpdateShoppingListJob` integration
