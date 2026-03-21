---
stepsCompleted: [1, 2, 3, 4, 5, 6, 7, 8]
inputDocuments:
  - 'specs/PRDs/nutriplan-phase8-sync-prd.md'
  - '_bmad-output/project-context.md'
workflowType: 'architecture'
project_name: 'NutriPlan'
user_name: 'Mrdth'
date: '2026-03-06'
lastStep: 8
status: 'complete'
completedAt: '2026-03-06'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

---

## Project Context Analysis

### Requirements Overview

**Functional Requirements:**
This PRD defines Phase 8: Shopping List Synchronization - a feature enhancement to the existing NutriPlan meal planning system. The core capability is automatic ingredient list updates when users add new meals marked for cooking to their existing meal plans.

Key functional requirements:
- **Trigger detection:** System must detect new meal additions (not updates/deletions) that fall within a shopping list's date range and are marked for cooking
- **Ingredient synchronization:** For each affected shopping list, increment quantities of existing ingredients and create new items for missing ingredients
- **Edge case handling:** Multiple overlapping shopping lists must all receive ingredient updates independently
- **Addition-only constraint:** Critical requirement that ingredients are never automatically removed, even when meals are deleted or cooking flags are toggled off

**Non-Functional Requirements:**
- **Performance:** Updates must complete within 5 seconds for typical scenarios, with 95th percentile processing under 2 seconds
- **Reliability:** 99% success rate measured over 30-day period, zero data loss (accidental removals) per 10,000 operations
- **Async processing:** Background processing without blocking user interactions
- **Scalability:** System must handle multiple overlapping list updates efficiently

### Scale & Complexity

- **Primary domain:** Backend service with event-driven architecture
- **Complexity level:** Medium (feature enhancement leveraging existing infrastructure)
- **Estimated architectural components:** 3-4 (Event system, Background processing service, Date range query optimization, UI feedback mechanism)

### Technical Constraints & Dependencies

**Constraints:**
- **Database schema:** No new schema changes required - leverages existing Phases 6-7 schema
- **Addition-only constraint:** Critical design principle that preserves user manual adjustments
- **Time-bound operations:** All updates must complete within 5 seconds

**Dependencies:**
- **Phase 7a:** Shopping List Generation (✅ Complete) - provides base shopping list functionality
- **Asynchronous processing system:** Laravel Queue (✅ Available) - required for background processing
- **Existing models:** MealPlan, MealPlanDay, MealAssignment, ShoppingList, ShoppingListItem, Recipe, Ingredient

### Cross-Cutting Concerns Identified

- **Event-driven architecture:** New meal creation must trigger list updates via events/observers
- **Data integrity:** Addition-only constraint requires careful state management to prevent accidental removals
- **Query optimization:** Date range filtering for affected shopping lists must be efficient
- **Error handling:** Failed background processing must not result in silent failures (retry mechanism + logging required)
- **Idempotency:** System must handle edge cases like overlapping lists without duplicate updates or conflicts

---

## Existing Architecture Foundation

**Note:** This is Phase 8 of an existing Laravel 12 + Vue 3 Inertia.js monolith. No starter template evaluation required - building on established architecture.

### Primary Technology Domain

**Backend Service with Event-Driven Architecture** - Feature enhancement to existing meal planning system

### Architecture Foundation

**Current Stack (Established):**

**Backend:**
- Laravel 12.0 framework with PHP 8.2+
- Service layer pattern for business logic encapsulation
- Eloquent ORM with existing models (MealPlan, MealPlanDay, MealAssignment, ShoppingList, ShoppingListItem, Recipe, Ingredient)
- Database Queue for async job processing
- Event system for cross-component communication

**Frontend:**
- Vue 3.5.13 with Composition API and TypeScript 5.2.2 (strict mode)
- Inertia.js 2.0 for server-side routing with SPA-like UX
- Tailwind CSS 3.4.1 with custom HSL color system
- Radix Vue 1.9.11 for UI primitives

**Infrastructure:**
- SQLite (default) with MySQL/PostgreSQL support
- Database Queue for background processing
- `composer dev` workflow for concurrent services

**Architectural Patterns Established:**
- Inertia.js Monolith pattern (server-side routing, Vue components, shared state)
- Service layer coordination between models and external services
- Event-driven architecture for decoupled component communication
- Factory pattern for test data (Pest PHP)
- Lazy loading for modals and heavy UI components

### Technical Constraints & Preferences

**From Project Context:**

**Must Follow:**
- TypeScript strict mode enabled
- `declare(strict_types=1);` on all PHP files
- Single-action controllers use `__invoke()` method
- Use `route()` helper from Ziggy for all URLs (never hardcode)
- Inertia's `useForm()` for form handling (not native fetch)
- Lazy load modals with `defineAsyncComponent()`
- Eager loading for Eloquent relationships (prevent N+1 queries)
- 100% type coverage enforced

**No Global State:**
- Use Inertia page props + local component state
- Shared state via composables in `/resources/js/composables/`

**File Organization:**
- UI components: `/resources/js/components/ui/` (Radix primitives - never modify)
- Feature components: `/resources/js/components/{FeatureName}/`
- Pages: `/resources/js/pages/`
- Composables: `/resources/js/composables/`
- Services: `app/Services/`

### Selected Starter: N/A (Existing Project)

**Rationale for Selection:**
This is Phase 8 of an existing production application (NutriPlan) with established Laravel 12 + Vue 3 + Inertia.js architecture. The PRD explicitly states "No new schema changes required. Leverages existing schema from Phases 6-7."

We are building on the existing, proven architecture rather than starting fresh. All technical decisions have been made and validated through Phases 1-7 of the project.

**Architectural Decisions Provided by Existing Foundation:**

**Language & Runtime:**
- PHP 8.2+ with strict types enabled
- TypeScript 5.2.2 with strict mode enabled

**Styling Solution:**
- Tailwind CSS 3.4.1 with custom HSL color tokens via CSS variables
- Radix Vue 1.9.11 for UI primitives

**Build Tooling:**
- Vite 6.2.0 with hot module replacement
- `composer dev` workflow for concurrent service development

**Testing Framework:**
- Backend: Pest PHP 3.7 with PHPStan level 5, Laravel Pint (PSR-12)
- Frontend: Vitest 3.0.9, ESLint + Prettier
- 100% type coverage enforced

**Code Organization:**
- Service layer pattern (`app/Services/`)
- MVC controllers with Inertia responses
- Eloquent models with relationships
- Vue Composition API with composables
- Feature-based component organization

**Development Experience:**
- Hot module replacement via Vite
- Inertia page props for server state
- Ziggy route generation
- Factory pattern for test data
- Conventional commits for version control

**Note:** Phase 8 implementation will follow established patterns from Phases 1-7, using existing service layer, queue infrastructure, and event system.

---

## Core Architectural Decisions

### Decision Priority Analysis

**Critical Decisions (Block Implementation):**
1. Event/Listener Architecture: MealAssignmentObserver pattern for meal creation detection
2. Service Layer Design: Single ShoppingListSyncService for all synchronization logic
3. Background Processing: One UpdateShoppingListJob per affected shopping list
4. Query Optimization: Add meal_plan_id foreign key to shopping_lists table
5. Error Handling: Custom retry logic for recoverable failures

**Important Decisions (Shape Architecture):**
6. UI Feedback: Toast notification using existing system (leverage existing infrastructure)

**Deferred Decisions (Post-MVP):**
- None identified - all decisions support Phase 8a core functionality

### Data Architecture

**Schema Addition (PRD Amendment Required):**
| Change | Type | Rationale |
|--------|------|-----------|
| Add `meal_plan_id` column | `foreignId` nullable, indexed | Enables direct MealPlan → ShoppingList queries |
| Add `mealPlan()` relationship | `belongsTo(MealPlan::class)` | Eloquent relationship for list lookup |

**Migration Required:**
```php
// database/migrations/YYYY_MM_DD_HHMMSS_add_meal_plan_id_to_shopping_lists_table.php
$table->foreignId('meal_plan_id')->nullable()->constrained()->after('user_id');
```

**Query Strategy:**
- **Before:** Date range overlap query with composite index
- **After:** `ShoppingList::where('meal_plan_id', $mealPlanId)->get()` with single index
- **Performance:** O(1) index lookup vs. O(n) date range scan

### Event System Design

**MealAssignmentObserver Pattern:**
```
MealAssignment::created()
  → MealAssignmentObserver@created
  → ShoppingListSyncService@syncNewMeal
  → Dispatch UpdateShoppingListJob for each affected list
```

**Event Characteristics:**
- Triggers ONLY on model creation (not updates/deletions)
- Checks `to_cook` flag before processing
- Loads MealPlan → MealPlanDay → MealAssignment chain
- Queries affected shopping lists by `meal_plan_id`

### Service Layer Design

**ShoppingListSyncService:**
```php
class ShoppingListSyncService
{
    public function syncNewMeal(MealAssignment $meal): void
    // 1. Validate meal is marked for cooking
    // 2. Get meal plan from meal->mealPlanDay->mealPlan
    // 3. Find all shopping lists for this meal plan
    // 4. Dispatch job for each list
}
```

**Single Responsibility:** Encapsulates all synchronization logic in one focused service

### Background Processing

**UpdateShoppingListJob Structure:**
```php
class UpdateShoppingListJob implements ShouldQueue
{
    public function __construct(public ShoppingList $list, public MealAssignment $meal)

    public function handle(): void
    // 1. Get ingredients from new meal
    // 2. For each ingredient:
    //    - If exists on list: increment quantity
    //    - If not exists: create new list item
}
```

**Queue Strategy:**
- One job per shopping list (parallel processing)
- Jobs are independent (failure isolation)
- Enables 95th percentile < 2 seconds processing

### Error Handling Strategy

**Custom Retry Logic:**
```php
class UpdateShoppingListJob implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [5, 15, 30]; // exponential backoff

    public function failed(Exception $exception): void
    // Log permanent failures to failed_jobs table
    // Dispatch alert if failure rate > 1%
}
```

**Recoverable Errors (Retry):**
- Database connection timeout
- Deadlock detection
- Queue worker timeout

**Permanent Failures (Log + Alert):**
- Invalid ingredient data
- Missing recipe relationships
- Business logic violations

**Success Metric:** 99% success rate over 30 days via failed_jobs monitoring

### UI Feedback Mechanism

**Toast Notification Flow:**
```
UpdateShoppingListJob completed
  → Fire ListUpdated event
  → Event listener broadcasts to user
  → Inertia middleware adds toast to page props
  → Frontend toast component displays for 3 seconds
```

**Message:** "List updated with new ingredients from [meal name]"

**Leverages:** Existing toast notification system

### Decision Impact Analysis

**Implementation Sequence:**
1. **Migration:** Add meal_plan_id column to shopping_lists
2. **Model:** Update ShoppingList with mealPlan() relationship
3. **Service:** Create ShoppingListSyncService
4. **Observer:** Create MealAssignmentObserver
5. **Job:** Create UpdateShoppingListJob with retry logic
6. **Event:** Wire up ListUpdated event for toast notifications
7. **Tests:** Unit and integration tests for sync flow
8. **PRD Update:** Document schema change addition

**Cross-Component Dependencies:**
- ShoppingList generation (Phase 7a) must populate meal_plan_id
- MealAssignment creation must trigger observer
- Queue worker must be running for async processing
- Existing toast system must support shopping list events

---

## Implementation Patterns & Consistency Rules

### Pattern Categories Defined

**Critical Conflict Points Identified:**
5 Phase 8-specific areas where AI agents could make different choices

### Naming Patterns

**Event Naming Convention:**
```php
// Use PascalCase, past tense for completed actions
RecipeImportCompleted     // ✅ Existing pattern
ShoppingListUpdated       // ✅ Phase 8 new event

// Event broadcastAs(): dot.notation lowercase
broadcastAs(): 'recipe.import.completed'  // ✅ Existing
broadcastAs(): 'shopping.list.updated'    // ✅ Phase 8
```

**Service Naming Convention:**
```php
// Existing: ShoppingListService (generation) - DO NOT MODIFY
// New: ShoppingListSyncService (synchronization) - SEPARATE SERVICE
ShoppingListService        // ✅ Existing - generateFromMealPlan()
ShoppingListSyncService    // ✅ Phase 8 - syncNewMeal()
```

**Job Naming Convention:**
```php
// PascalCase, describe WHAT is being done
ImportRecipeJob           // ✅ Existing
UpdateShoppingListJob     // ✅ Phase 8
```

**Observer Naming Convention:**
```php
// Singular model name + Observer
MealAssignmentObserver     // ✅ Phase 8 (first observer in project)
```

### Structure Patterns

**File Organization:**
```
app/
├── Observers/
│   └── MealAssignmentObserver.php       # Phase 8: First observer
├── Services/
│   ├── ShoppingListService.php          # Existing: DO NOT MODIFY
│   └── ShoppingListSyncService.php      # Phase 8: NEW - sync logic
├── Jobs/
│   ├── ImportRecipeJob.php              # Existing
│   └── UpdateShoppingListJob.php        # Phase 8
├── Events/
│   ├── RecipeImportCompleted.php        # Existing
│   └── ShoppingListUpdated.php          # Phase 8
└── Models/
    └── ShoppingList.php                 # MODIFY: Add mealPlan() relationship
```

**Service Separation:**
- **ShoppingListService** (existing): `generateFromMealPlan()`, `prepareForDisplay()` - List creation and display
- **ShoppingListSyncService** (new): `syncNewMeal()` - List synchronization logic
- **Rationale:** Separate concerns, avoids modifying existing tested code

### Format Patterns

**Job Constructor Pattern:**
```php
// Modern PHP 8.2+ pattern - public readonly properties
class UpdateShoppingListJob implements ShouldQueue
{
    public function __construct(
        public readonly ShoppingList $shoppingList,
        public readonly MealAssignment $meal
    ) {}
}
```

**Event Constructor Pattern:**
```php
// Broadcasting event with public readonly properties
class ShoppingListUpdated implements ShouldBroadcast
{
    public function __construct(
        public readonly int $userId,
        public readonly string $message,
        public readonly int $shoppingListId
    ) {}
}
```

**Service Method Pattern:**
```php
// Single public method, typed parameters and return
class ShoppingListSyncService
{
    public function syncNewMeal(MealAssignment $meal): void
    {
        // Implementation
    }
}
```

### Communication Patterns

**Observer Event Flow:**
```php
// MealAssignmentObserver@created
public function created(MealAssignment $meal): void
{
    // 1. Check if meal is for cooking
    if (!$meal->to_cook) {
        return;
    }

    // 2. Delegate to service
    app(ShoppingListSyncService::class)->syncNewMeal($meal);
}
```

**Event Dispatch Pattern:**
```php
// After job completes successfully
ShoppingListUpdated::dispatch(
    userId: $shoppingList->user_id,
    message: "List updated with new ingredients from {$meal->mealPlanRecipe->recipe->title}",
    shoppingListId: $shoppingList->id
);
```

**Event Broadcast Structure:**
```php
public function broadcastWith(): array
{
    return [
        'message' => $this->message,
        'shoppingListId' => $this->shoppingListId,
    ];
}

public function broadcastOn(): array
{
    return [new PrivateChannel('user.' . $this->userId)];
}

public function broadcastAs(): string
{
    return 'shopping.list.updated';
}
```

### Process Patterns

**Error Handling Pattern:**
```php
class UpdateShoppingListJob implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [5, 15, 30]; // seconds

    public function failed(Throwable $exception): void
    {
        Log::error('Shopping list update failed', [
            'shopping_list_id' => $this->shoppingList->id,
            'meal_assignment_id' => $this->meal->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

**Logging Pattern:**
```php
// Structured logging with context
Log::info('Shopping list sync initiated', [
    'meal_assignment_id' => $meal->id,
    'meal_plan_id' => $mealPlanId,
    'affected_lists' => $shoppingLists->count(),
]);

Log::error('Shopping list sync failed', [
    'shopping_list_id' => $list->id,
    'error' => $exception->getMessage(),
]);
```

**Database Query Pattern:**
```php
// Eager loading to prevent N+1 queries
$meal->load('mealPlanDay.mealPlan', 'mealPlanRecipe.recipe.ingredients');

// Indexed query for performance
ShoppingList::where('meal_plan_id', $mealPlanId)
    ->where('user_id', $userId)
    ->get();
```

### Enforcement Guidelines

**All AI Agents MUST:**

1. **Use existing patterns** - Check for existing implementations before creating new ones
2. **Separate services** - Create `ShoppingListSyncService`, do NOT modify `ShoppingListService`
3. **Follow event naming** - PascalCase class, dot.notation for `broadcastAs()`
4. **Public readonly props** - Use PHP 8.2+ constructor property promotion for Jobs and Events
5. **Eager loading** - Always load relationships to prevent N+1 queries
6. **Structured logging** - Use associative arrays for log context
7. **Type declarations** - All parameters and return types must be declared
8. **Nullable relationships** - `meal_plan_id` is nullable, handle null cases

**Pattern Enforcement:**

- **Review existing files** before creating new ones
- **Match existing code style** (PSR-12 via Laravel Pint)
- **Run tests** - Use Pest for backend, Vitest for frontend
- **Type checking** - PHPStan level 5, TypeScript strict mode

### Pattern Examples

**Good Examples:**

```php
// ✅ Correct: Observer pattern
class MealAssignmentObserver
{
    public function created(MealAssignment $meal): void
    {
        if (!$meal->to_cook) {
            return;
        }
        app(ShoppingListSyncService::class)->syncNewMeal($meal);
    }
}

// ✅ Correct: Job with modern PHP 8.2+ syntax
class UpdateShoppingListJob implements ShouldQueue
{
    public function __construct(
        public readonly ShoppingList $list,
        public readonly MealAssignment $meal
    ) {}

    public $tries = 3;
    public $backoff = [5, 15, 30];
}

// ✅ Correct: Event with broadcast
class ShoppingListUpdated implements ShouldBroadcast
{
    public function __construct(
        public readonly int $userId,
        public readonly string $message,
        public readonly int $shoppingListId
    ) {}

    public function broadcastAs(): string
    {
        return 'shopping.list.updated';
    }
}
```

**Anti-Patterns:**

```php
// ❌ Wrong: Modifying existing ShoppingListService
class ShoppingListService
{
    // DO NOT ADD syncNewMeal() here
    // Use separate ShoppingListSyncService
}

// ❌ Wrong: Old constructor style
class UpdateShoppingListJob implements ShouldQueue
{
    private $list;
    private $meal;

    public function __construct(ShoppingList $list, MealAssignment $meal)
    {
        $this->list = $list;
        $this->meal = $meal;
    }
    // Use public readonly constructor promotion instead
}

// ❌ Wrong: Not eager loading
$mealPlanId = $meal->mealPlanDay->meal_plan_id; // N+1 query risk
// Use: $meal->load('mealPlanDay.mealPlan')
```

---

## Project Structure & Boundaries

### Phase 8 Additions to Existing Structure

**Note:** This is Phase 8 of an existing Laravel 12 + Vue 3 project. Only new files and modifications are shown.

```
NutriPlan/
├── app/
│   ├── Models/
│   │   └── ShoppingList.php                    # MODIFY: Add mealPlan() relationship
│   │
│   ├── Observers/                              # NEW: First observers in project
│   │   └── MealAssignmentObserver.php          # NEW: Watches for meal creation
│   │
│   ├── Services/
│   │   ├── ShoppingListService.php             # EXISTING: DO NOT MODIFY
│   │   └── ShoppingListSyncService.php         # NEW: Synchronization logic
│   │
│   ├── Jobs/
│   │   ├── ImportRecipeJob.php                 # EXISTING
│   │   └── UpdateShoppingListJob.php           # NEW: Background list updates
│   │
│   ├── Events/
│   │   ├── RecipeImportCompleted.php           # EXISTING
│   │   └── ShoppingListUpdated.php             # NEW: Toast notification event
│   │
│   └── Providers/
│       └── EventServiceProvider.php            # MODIFY: Register observer
│
├── database/
│   ├── migrations/
│   │   ├── 2025_04_06_070727_create_shopping_lists_table.php      # EXISTING
│   │   ├── 2025_04_06_070733_create_shopping_list_items_table.php # EXISTING
│   │   ├── 2025_04_06_093303_add_date_fields_to_shopping_lists_table.php # EXISTING
│   │   ├── 2025_04_12_161800_add_order_column_to_shopping_list_items_table.php # EXISTING
│   │   └── YYYY_MM_DD_HHMMSS_add_meal_plan_id_to_shopping_lists_table.php # NEW
│   │
│   └── seeders/                                # EXISTING: No changes
│
├── tests/
│   ├── Unit/
│   │   ├── Services/                           # EXISTING
│   │   └── ShoppingListSyncServiceTest.php     # NEW: Sync service tests
│   │
│   ├── Feature/
│   │   ├── ShoppingList/                       # EXISTING
│   │   └── MealAssignmentSyncTest.php          # NEW: End-to-end sync tests
│   │
│   └── Pest.php                                # EXISTING: No changes
│
├── resources/js/
│   ├── composables/                            # EXISTING: Use existing toast composable
│   ├── pages/ShoppingLists/
│   │   └── [id].tsx                            # EXISTING: May add toast listener
│   └── components/
│       └── ui/                                 # EXISTING: Toast component already exists
│
├── routes/                                     # EXISTING: No new routes for Phase 8a
│
├── specs/
│   ├── PRDs/
│   │   └── nutriplan-phase8-sync-prd.md        # UPDATE: Add schema change note
│   │
│   └── meal-planning-phase-8-automatic-synchronization.md # EXISTING: Technical spec
│
└── _bmad-output/
    └── planning-artifacts/
        └── architecture.md                     # THIS DOCUMENT: Phase 8 architecture
```

### Architectural Boundaries

**Service Boundaries:**
```
┌─────────────────────────────────────────────────────────────┐
│                    HTTP Layer                              │
│  ┌───────────────────────────────────────────────────────┐  │
│  │              MealAssignmentController                 │  │
│  │                   (creates meal)                      │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                   Observer Layer                           │
│  ┌───────────────────────────────────────────────────────┐  │
│  │          MealAssignmentObserver@created               │  │
│  │     (listens for MealAssignment::created events)      │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                   Service Layer                            │
│  ┌───────────────────────────────────────────────────────┐  │
│  │         ShoppingListSyncService@syncNewMeal           │  │
│  │     (finds affected lists, dispatches jobs)           │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    Queue Layer                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │           UpdateShoppingListJob@handle                │  │
│  │     (updates individual list items)                   │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    Data Layer                              │
│  ┌────────────────┐  ┌────────────────┐  ┌──────────────┐  │
│  │ ShoppingList   │  │ShoppingListItem │  │ MealAssignment│  │
│  │ (meal_plan_id) │  │  (increment)    │  │  (trigger)    │  │
│  └────────────────┘  └────────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

**Event Flow Boundaries:**
```
MealAssignment::created (Eloquent Event)
         │
         ▼
MealAssignmentObserver@created (Laravel Observer)
         │
         ▼
ShoppingListSyncService@syncNewMeal (Service Layer)
         │
         ├─── UpdateShoppingListJob::dispatch() ────┐
         ├─── UpdateShoppingListJob::dispatch() ────┼─── (One per list)
         └─── UpdateShoppingListJob::dispatch() ────┘
                                                    │
                                                    ▼
                                    ┌───────────────────────┐
                                    │ Queue Worker Process  │
                                    └───────────────────────┘
                                                    │
                                                    ▼
                                    UpdateShoppingListJob@handle
                                                    │
                                                    ▼
                                    ShoppingListUpdated::dispatch
                                                    │
                                                    ▼
                                    ┌───────────────────────────┐
                                    │ PrivateChannel(user.{id}) │
                                    │ broadcastAs(): shopping.  │
                                    │       list.updated        │
                                    └───────────────────────────┘
                                                    │
                                                    ▼
                                    ┌───────────────────────────┐
                                    │ Frontend Toast Component  │
                                    │ (displays 3 seconds)      │
                                    └───────────────────────────┘
```

### Requirements to Structure Mapping

**Phase 8 Requirements to Files:**

| PRD Requirement | Component | File Location |
|----------------|-----------|----------------|
| Detect new meal additions | Observer | `app/Observers/MealAssignmentObserver.php` |
| Check if meal is for cooking | Service | `app/Services/ShoppingListSyncService.php` |
| Find affected shopping lists | Service | `app/Services/ShoppingListSyncService.php` |
| Increment existing items | Job | `app/Jobs/UpdateShoppingListJob.php` |
| Create new list items | Job | `app/Jobs/UpdateShoppingListJob.php` |
| Background processing | Job | `app/Jobs/UpdateShoppingListJob.php` |
| Visual indicator for updates | Event | `app/Events/ShoppingListUpdated.php` |
| Add meal_plan_id relationship | Model | `app/Models/ShoppingList.php` |
| Index for performance | Migration | `database/migrations/*_add_meal_plan_id_*.php` |

**Cross-Cutting Concerns:**

| Concern | Implementation | Location |
|---------|----------------|----------|
| Event/Listener registration | Observer auto-discovery | `app/Providers/EventServiceProvider.php` |
| Queue configuration | Database queue (existing) | `.env` + `config/queue.php` |
| Error handling | Job retry + logging | `app/Jobs/UpdateShoppingListJob.php` |
| User notifications | Broadcasting to private channel | `app/Events/ShoppingListUpdated.php` |

### Integration Points

**Internal Communication:**

1. **MealAssignment Creation → Observer**
   - Trigger: Eloquent `created` event
   - Boundary: Observer pattern (decoupled)
   - Data passed: `MealAssignment` model instance

2. **Observer → Service**
   - Method call: `ShoppingListSyncService::syncNewMeal()`
   - Boundary: Service layer (business logic)
   - Data passed: `MealAssignment` with loaded relationships

3. **Service → Jobs**
   - Dispatch: `UpdateShoppingListJob::dispatch()`
   - Boundary: Queue (async processing)
   - Data passed: `ShoppingList` + `MealAssignment` models

4. **Job → Event**
   - Dispatch: `ShoppingListUpdated::dispatch()`
   - Boundary: Broadcasting system
   - Data passed: `userId`, `message`, `shoppingListId`

**External Integrations:**

- **Pusher** (optional): Real-time broadcast of `ShoppingListUpdated` event
  - Already configured in project for `RecipeImportCompleted`
  - Reuse existing broadcasting configuration

**Data Flow:**

```
User adds meal to plan
         │
         ▼
MealAssignment created in DB
         │
         ▼
Observer detects creation
         │
         ▼
Service finds affected lists (by meal_plan_id)
         │
         ▼
Job dispatched for each list (queued)
         │
         ▼
Worker processes job (async)
         │
         ▼
Shopping list items updated (increment/create)
         │
         ▼
Event broadcast to user's channel
         │
         ▼
Frontend displays toast notification
```

### File Organization Patterns

**New Files Created:**
- `app/Observers/` - First observer directory in project
- `app/Services/ShoppingListSyncService.php` - Separate from existing ShoppingListService
- `app/Jobs/UpdateShoppingListJob.php` - Follows existing job pattern
- `app/Events/ShoppingListUpdated.php` - Follows existing event pattern

**Files Modified:**
- `app/Models/ShoppingList.php` - Add `mealPlan()` relationship
- `app/Providers/EventServiceProvider.php` - Register observer
- `database/migrations/*_add_meal_plan_id_*.php` - New migration

**Tests Created:**
- `tests/Unit/Services/ShoppingListSyncServiceTest.php` - Service logic tests
- `tests/Feature/MealAssignmentSyncTest.php` - End-to-end sync tests

### Development Workflow Integration

**Development Server Structure:**
```bash
# Start all services (existing pattern)
composer dev
# - Laravel server: http://localhost:8000
# - Vite HMR: http://localhost:5173
# - Queue worker: php artisan queue:work
```

**Build Process Structure:**
- No frontend build changes (toast component exists)
- No additional build steps required
- Existing Vite configuration unchanged

**Deployment Structure:**
- Migration runs: `php artisan migrate`
- Queue worker required: `php artisan queue:work --daemon`
- Broadcasting configured via Pusher credentials

---

## Architecture Validation Results

### Coherence Validation ✅

**Decision Compatibility:**
- ✅ All technology choices are compatible with existing Laravel 12 + Vue 3 stack
- ✅ Observer pattern integrates seamlessly with existing Eloquent ORM
- ✅ Queue jobs use existing database queue infrastructure
- ✅ Event broadcasting leverages existing Pusher configuration
- ✅ No contradictory decisions identified

**Pattern Consistency:**
- ✅ Implementation patterns follow existing codebase conventions (PSR-12, PHP 8.2+ features)
- ✅ Naming conventions align with established patterns (PascalCase classes, dot.notation broadcasts)
- ✅ Service separation maintains single responsibility principle
- ✅ Job/Event patterns match existing `ImportRecipeJob` and `RecipeImportCompleted`

**Structure Alignment:**
- ✅ Project structure additions integrate cleanly with existing directories
- ✅ New `app/Observers/` directory follows Laravel conventions
- ✅ Service layer separation respects existing boundaries
- ✅ Integration points properly structured (Observer → Service → Job → Event)

### Requirements Coverage Validation ✅

**Functional Requirements Coverage:**

| PRD Requirement | Architectural Support | Status |
|----------------|----------------------|--------|
| Detect new meal additions | `MealAssignmentObserver@created` | ✅ |
| Check if meal is for cooking | `to_cook` flag check in service | ✅ |
| Find affected shopping lists | `ShoppingList::where('meal_plan_id')` | ✅ |
| Increment existing items | Job handles quantity updates | ✅ |
| Create new list items | Job creates missing items | ✅ |
| Multiple overlapping lists | One job per list (parallel) | ✅ |
| Background processing | Laravel Queue with `ShouldQueue` | ✅ |
| Complete within 5 seconds | Parallel jobs + indexed query | ✅ |
| 95th percentile < 2 seconds | Single job per list + O(1) query | ✅ |
| Visual indicator for updates | `ShoppingListUpdated` event + toast | ✅ |
| Addition-only constraint | No removal logic in job | ✅ |

**Non-Functional Requirements Coverage:**

| NFR | Architectural Support | Status |
|-----|----------------------|--------|
| 99% success rate | Retry logic (3 attempts) + error logging | ✅ |
| Zero data loss | Addition-only + failed_jobs tracking | ✅ |
| Async processing | Laravel Queue + background workers | ✅ |
| Idempotency | Jobs only on creation, not updates | ✅ |
| Performance | Indexed `meal_plan_id` column | ✅ |

### Implementation Readiness Validation ✅

**Decision Completeness:**
- ✅ All critical decisions documented with clear rationale
- ✅ Technology versions specified (Laravel 12, PHP 8.2+, Vue 3.5.13)
- ✅ Schema change documented (meal_plan_id addition)
- ✅ Integration patterns fully specified

**Structure Completeness:**
- ✅ Complete file tree defined with 6 new files
- ✅ Component boundaries clearly established
- ✅ Integration points mapped (Observer → Service → Job → Event)
- ✅ Requirements mapped to specific file locations

**Pattern Completeness:**
- ✅ All 5 Phase 8-specific conflict points addressed
- ✅ Naming conventions comprehensive (Events, Services, Jobs, Observers)
- ✅ Communication patterns specified (event dispatch, logging, queries)
- ✅ Process patterns documented (error handling, retry logic)
- ✅ Good examples and anti-patterns provided

### Gap Analysis Results

**Critical Gaps:** None identified

**Important Gaps:** None identified

**Nice-to-Have Gaps:**
- Consider adding monitoring/metrics for sync success rate (can be added post-MVP)
- Consider adding admin dashboard for failed job review (can be added post-MVP)

### Validation Issues Addressed

**Issue 1: PRD Schema Change**
- **Finding:** PRD states "No new schema changes required" but our decision adds `meal_plan_id`
- **Resolution:** Documented as PRD amendment requirement. Architectural improvement justifies the change.
- **Status:** ✅ Resolved - Update PRD to reflect schema addition

**Issue 2: Observer Registration**
- **Finding:** First observer in project - need to ensure proper registration
- **Resolution:** Documented in `EventServiceProvider.php` modification requirements
- **Status:** ✅ Resolved - Registration pattern documented

### Architecture Completeness Checklist

**✅ Requirements Analysis**
- [x] Project context thoroughly analyzed
- [x] Scale and complexity assessed (Medium, feature enhancement)
- [x] Technical constraints identified (no schema changes, <5 second requirement)
- [x] Cross-cutting concerns mapped (event-driven, data integrity, query optimization)

**✅ Architectural Decisions**
- [x] Critical decisions documented with versions
- [x] Technology stack fully specified (leverages existing Laravel 12 + Vue 3)
- [x] Integration patterns defined (Observer → Service → Job → Event)
- [x] Performance considerations addressed (indexed meal_plan_id, parallel jobs)

**✅ Implementation Patterns**
- [x] Naming conventions established (PascalCase classes, dot.notation broadcasts)
- [x] Structure patterns defined (service separation, observer registration)
- [x] Communication patterns specified (event dispatch, structured logging)
- [x] Process patterns documented (retry logic, error handling)

**✅ Project Structure**
- [x] Complete directory structure defined (6 new files, 3 modifications)
- [x] Component boundaries established (HTTP → Observer → Service → Queue → Data)
- [x] Integration points mapped (Eloquent events, Queue dispatch, Broadcasting)
- [x] Requirements to structure mapping complete

### Architecture Readiness Assessment

**Overall Status:** ✅ **READY FOR IMPLEMENTATION**

**Confidence Level:** **HIGH** - All validation checks passed, no blocking issues identified

**Key Strengths:**
1. Clean integration with existing Laravel architecture
2. Follows established project patterns (Jobs, Events, Services)
3. Performance-oriented design (indexed queries, parallel processing)
4. Comprehensive error handling and retry logic
5. Clear separation of concerns (Observer → Service → Job)
6. Addition-only constraint enforced by design

**Areas for Future Enhancement:**
- Add metrics/monitoring dashboard for sync success rate
- Consider admin UI for reviewing failed jobs
- Add detailed logging per list item for debugging

### Implementation Handoff

**AI Agent Guidelines:**
1. Follow all architectural decisions exactly as documented
2. Use implementation patterns consistently (public readonly props, structured logging)
3. Respect service separation - do NOT modify existing `ShoppingListService`
4. Refer to this document for all architectural questions
5. Run tests after each component implementation

**First Implementation Priority:**
```bash
# 1. Create migration for meal_plan_id column
php artisan make:migration add_meal_plan_id_to_shopping_lists_table

# 2. Create observer (first in project)
php artisan make:observer MealAssignmentObserver --model=MealAssignment

# 3. Run tests
composer test
```

**Implementation Sequence:**
1. Migration: Add `meal_plan_id` column to `shopping_lists`
2. Model: Update `ShoppingList` with `mealPlan()` relationship
3. Service: Create `ShoppingListSyncService`
4. Observer: Create `MealAssignmentObserver`
5. Job: Create `UpdateShoppingListJob` with retry logic
6. Event: Wire up `ShoppingListUpdated` event for toast notifications
7. Tests: Unit and integration tests for sync flow
8. PRD Update: Document schema change addition
