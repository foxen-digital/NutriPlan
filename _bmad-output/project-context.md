---
project_name: 'NutriPlan'
user_name: 'Mrdth'
date: '2026-03-25'
sections_completed:
  ['technology_stack', 'language_specific_rules', 'framework_specific_rules', 'testing_rules', 'code_quality_rules', 'workflow_rules', 'critical_rules']
status: 'complete'
rule_count: 67
optimized_for_llm: true
existing_patterns_found: 10
---

# Project Context for AI Agents

_This file contains critical rules and patterns that AI agents must follow when implementing code in this project. Focus on unobvious details that agents might otherwise miss._

---

## Technology Stack & Versions

### Backend
- PHP 8.4 required
- Laravel 13.0 framework
- Inertia.js 3.0 (Laravel adapter)
- Livewire 4.0
- Laravel Sanctum 4.0
- Laravel Pulse 1.7
- Spatie Laravel Sluggable 3.7
- Ziggy 2.4 for route generation
- mrdth/laravel-model-settings 2.0
- pusher/pusher-php-server 7.2

### Frontend
- Vue 3.5.13 with Composition API
- TypeScript 5.2.2 (strict mode enabled)
- @inertiajs/vue3 3.0
- Vite 6.2.0 with hot module replacement
- Tailwind CSS 3.4.1 with custom HSL color system
- Radix Vue 1.9.11 for UI primitives
- Lucide Vue Next 0.468.0 for icons
- @vueuse/core 12.0 for composable utilities
- vuedraggable 4.1 for drag-and-drop
- motion-v 1.0-beta for animations
- @ericblade/quagga2 1.8 for barcode scanning
- markdown-it + vue-markdown-render for markdown

### Testing & Quality
- Backend: Pest PHP 4.0, Larastan 3.2, Laravel Pint (PSR-12)
- Frontend: Vitest 3.0.9 + @testing-library/vue, ESLint + Prettier
- Type coverage requirement: 100% (enforced in Pest)

### Critical Architecture Notes
- **Ziggy Integration**: Use `route()` helper for all URLs, never hardcode
- **Inertia v3**: `Inertia::lazy()` removed — use `Inertia::optional()` instead
- **Inertia v3**: Axios installed separately but Inertia uses its own XHR client by default
- **HSL Color System**: Use semantic CSS variable tokens, not arbitrary hex values
- **Radix Vue Primitives**: Located in `/resources/js/components/ui/` — never modify directly

## Critical Implementation Rules

### Language-Specific Rules

#### TypeScript
- **Strict mode enabled** — `strict: true` enables ALL strict checks
- **Define interfaces for all Vue component props** before `defineProps<Props>()`
- **Use `@/` alias** for all imports within `resources/js/`
- **Import Radix components explicitly**: `import { Button } from '@/components/ui/button'`
- **Route generation uses `route()` from ziggy-js**, never hardcode URLs
- **Lazy load modals**: `const Modal = defineAsyncComponent(() => import('@/components/...'))`
- **isolatedModules: true** — each file must be transpilable independently
- **`@typescript-eslint/no-explicit-any` is off** — but use `any` sparingly and intentionally

#### PHP
- **Every PHP file MUST start with** `declare(strict_types=1);` (line 1, no exceptions)
- **All classes use PSR-4 autoloading** with proper namespacing (`App\...`)
- **Single-action controllers use `__invoke()` method** — no suffix (e.g., `RecipeSearchController`)
- **Constructor property promotion** for dependency injection: `public function __construct(public GitHub $github) {}`
- **No empty zero-parameter `__construct()`** unless the constructor is private
- **Type hints required** on all method signatures and return types
- **Use TitleCase for Enum keys**: `FavoritePerson`, `Monthly`
- **PHPDoc blocks** preferred over inline comments

#### Vue Components
- **Use `<script setup lang="ts">` format exclusively** — no Options API, no JS-only files
- **Define interfaces for props before `defineProps<Props>()`**
- **Use Composition API** with composables from `@/composables/`
- **Use `useForm()` from `@inertiajs/vue3`** for form handling — not native fetch
- **Barrel exports for UI components** — import from `@/components/ui/button`, not direct paths
- **Single root element required** in all Vue components

### Framework-Specific Rules

#### Vue 3 + Composition API
- **Import icons from lucide-vue-next**: `import { IconName } from 'lucide-vue-next'`
- **Use Inertia's `<Link>` component** for navigation, never `<a>` tags
- **Form handling via `useForm()` from `@inertiajs/vue3`**:
  ```ts
  const form = useForm({ field: value });
  form.post(route('route.name'), { onSuccess: () => {} });
  ```
- **Modals use Radix Dialog**: `import { Dialog, DialogContent } from '@/components/ui/dialog'`
- **Use `@vueuse/core`** for common composable utilities before writing custom ones
- **Drag-and-drop via `vuedraggable`** — check existing patterns before implementing custom DnD
- **Animations via `motion-v`** — do not use raw CSS transitions for complex animations
- **Barcode scanning via `@ericblade/quagga2`** — use `BarcodeService` pattern
- **Markdown rendering via `vue-markdown-render`** — do not use `v-html` directly for user content

#### Laravel + Inertia v3
- **Single-action controllers** use `__invoke()` with no method suffix
- **Inertia responses**: `return inertia('PageComponent', ['prop' => $value]);`
- **`Inertia::lazy()` is REMOVED** — use `Inertia::optional()` for optional props
- **`router.cancel()` is REMOVED** — use `router.cancelAll()` in v3
- **Deferred props** require an empty/skeleton state with pulsing animation while loading
- **Axios is available** (installed separately) but Inertia v3 uses its own XHR client by default
- **Use route model binding with slugs** for public URLs
- **API routes return JSON**: `return response()->json(['data' => $data]);`
- **Eloquent API Resources** for all API responses — always versioned

#### Livewire v4
- **Livewire components** live in `app/Livewire/` and `resources/views/livewire/`
- **Used primarily for Pulse dashboard cards** — not general UI (Inertia/Vue handles that)

#### State Management
- **No global state library** — use Inertia page props + local component state
- **Shared state via composables** in `/resources/js/composables/`
- **Reactive data** with `ref()` and `computed()` from Vue
- **Model settings** via `mrdth/laravel-model-settings` — check existing usage before rolling custom settings

#### Performance Rules
- **Always lazy load modals** using `defineAsyncComponent()`
- **Always eager-load Eloquent relationships** to prevent N+1 queries
- **Use `select()` on queries** to limit columns retrieved

### Testing Rules

#### Backend Testing (Pest PHP 4)
- **Use `it()` or `test()` function** — NOT PHPUnit `@test` annotation
- **Use factories for all test data**: `Recipe::factory()->create(['field' => 'value'])`
- **Chain factory states**: `User::factory()->has(Recipe::factory()->count(3))->create()`
- **Inertia page assertions**:
  ```php
  $response->assertInertia(fn (Assert $page) => $page
      ->component('ComponentName')
      ->has('data', 3)
      ->where('data.0.field', 'value')
  );
  ```
- **Feature tests** go in `tests/Feature/`, **unit tests** in `tests/Unit/`
- **Create tests via artisan**: `php artisan make:test --pest {Name}`
- **Run tests**: `php artisan test --compact` or `php artisan test --compact --filter=testName`
- **Run minimum tests needed** — use filename or filter, not the full suite
- **Do NOT delete tests** without explicit user approval
- **Use `timacdonald/log-fake`** for asserting log output in tests

#### Frontend Testing (Vitest 3)
- **Use `@testing-library/vue`** for component testing
- **Test files use `.test.ts` or `.spec.ts` extension**
- **Coverage reports** output to `./coverage/js`

#### Coverage Requirements
- **100% type coverage enforced** — Pest fails if below minimum
- **`composer test`** runs full suite: type coverage, unit tests, linting, refactor checks
- **Every change must be tested** — write or update a test, then run it to confirm it passes

#### Test Data Patterns
- **Check factory states** before manually setting up model attributes
- **Use `fake()` helper** (not `$this->faker`) — follow existing convention in the file

### Code Quality & Style Rules

#### Linting & Formatting
- **Run `vendor/bin/pint --dirty --format agent`** after modifying any PHP files
- **Do NOT run `pint --test`** — run pint directly to fix issues
- **Prettier config**: semi-colons, single quotes, 150 char width, 4-space tabs
- **ESLint ignores** UI components (`resources/js/components/ui/*`) — do not lint these
- **Run `composer lint`** to fix all style issues (pint + prettier + eslint)

#### File Organization
- **UI primitives**: `resources/js/components/ui/` — Radix-based, never modify
- **Feature components**: `resources/js/components/{FeatureName}/`
- **Pages**: `resources/js/pages/`
- **Composables**: `resources/js/composables/`
- **Types**: `resources/js/types/`
- **Actions**: `app/Actions/` — single-purpose action classes
- **Services**: `app/Services/` — reusable business logic
- **Policies**: `app/Policies/` — always use for authorization
- **Enums**: `app/Enums/`
- **Value Objects**: `app/ValueObjects/`
- **Do not create new top-level directories** without user approval

#### Naming Conventions
- **PHP classes**: PascalCase (`RecipeSearchController`)
- **PHP methods/variables**: camelCase (`getUserRecipes`, `$recipeId`)
- **Vue components**: PascalCase files (`RecipeCard.vue`)
- **Composables**: camelCase prefixed with `use` (`useRecipeSearch.ts`)
- **Descriptive names**: `isRegisteredForDiscounts` not `discount()`

#### Documentation
- **PHPDoc blocks** for PHP methods (especially public APIs)
- **TypeScript types** preferred over JSDoc comments
- **No excessive comments** — only add inline comments for non-obvious logic
- **Do not create documentation files** unless explicitly requested

### Development Workflow Rules

#### Local Development
- **Run `composer dev`** to start all services (server, queue, logs, vite) concurrently
- **Run `composer dev:ssr`** for SSR development mode
- **Sail** available for Docker-based environment

#### Git Conventions
- **Main branch**: `main` — all PRs target this branch
- **Feature branches**: Conventional naming (`feature/`, `bugfix/`, `hotfix/`, `chore/`)
- **Commit messages**: Conventional Commits preferred; shorter ok for small changes

#### Code Quality Gates
- **`composer test`** must pass before marking work complete
- **Includes**: type coverage, PHP tests, type checking, JS tests, linting, refactor checks
- **`php artisan test --compact`** for quick PHP-only test runs during development

#### Artisan Conventions
- **Always pass `--no-interaction`** to artisan make commands
- **Use `php artisan make:` commands** to create controllers, models, migrations, etc.
- **Use `php artisan make:class`** for generic PHP classes
- **Inspect routes**: `php artisan route:list --except-vendor`
- **Read config values**: `php artisan config:show app.name`

### Critical Don't-Miss Rules

#### Anti-Patterns to Avoid
- **NEVER hardcode URLs** — always use `route()` helper from ziggy-js
- **NEVER use Options API** — Composition API with `<script setup lang="ts">` only
- **NEVER skip `declare(strict_types=1)`** in PHP files — must be line 1
- **NEVER use `Inertia::lazy()`** — removed in v3; use `Inertia::optional()` instead
- **NEVER use `router.cancel()`** — replaced by `router.cancelAll()` in Inertia v3
- **NEVER use native fetch for forms** — use Inertia's `useForm()` for form submissions
- **NEVER modify Radix UI components** in `resources/js/components/ui/` directly
- **NEVER use `any` type indiscriminately** — strict mode catches unintended any
- **NEVER use `v-html`** for user-provided markdown — use `vue-markdown-render` instead
- **NEVER create verification scripts** when tests cover the functionality

#### Edge Cases to Handle
- **Nullable numeric values**: Ingredient amounts can be 0 or null — handle both
- **Optimistic UI updates**: For toggles (favorites, purchased), update UI first then sync
- **Date filtering**: Meal plans should filter out ended plans when shown for selection
- **Empty collections**: Handle gracefully when users have no collections/meal plans
- **Deferred Inertia props**: Always provide a skeleton/pulsing placeholder while loading

#### Security Rules
- **Always verify user ownership** before modifications — use Laravel policies
- **Never trust client-side data** — always validate server-side
- **Use Laravel's built-in auth** — never roll custom authentication
- **API routes protected by Sanctum** — verify token auth middleware on all API routes

#### Performance Gotchas
- **Always lazy-load modals** with `defineAsyncComponent()` — don't bundle everything
- **Always eager-load Eloquent relationships** — prevent N+1 queries
- **Use `select()` on queries** to limit columns retrieved
- **Check `@vueuse/core`** before writing custom composables for common utilities

---

## Usage Guidelines

**For AI Agents:**

- Read this file before implementing any code
- Follow ALL rules exactly as documented
- When in doubt, prefer the more restrictive option
- Update this file if new patterns emerge

**For Humans:**

- Keep this file lean and focused on agent needs
- Update when technology stack changes
- Review quarterly for outdated rules
- Remove rules that become obvious over time

Last Updated: 2026-03-25
