---
project_name: 'NutriPlan'
user_name: 'Mrdth'
date: '2026-03-03'
sections_completed:
  ['technology_stack', 'language_specific_rules', 'framework_specific_rules', 'testing_rules', 'code_quality_rules', 'workflow_rules', 'critical_rules']
status: 'complete'
rule_count: 54
optimized_for_llm: true
existing_patterns_found: 8
---

# Project Context for AI Agents

_This file contains critical rules and patterns that AI agents must follow when implementing code in this project. Focus on unobvious details that agents might otherwise miss._

---

## Technology Stack & Versions

### Backend
- PHP 8.2+ required
- Laravel 12.0 framework
- Inertia.js 2.0 (Laravel adapter)
- Spatie Laravel Sluggable 3.7
- Ziggy 2.4 for route generation
- SQLite (default), supports MySQL/PostgreSQL

### Frontend
- Vue 3.5.13 with Composition API
- TypeScript 5.2.2 (strict mode enabled)
- Inertia.js Vue3 2.0-beta.3
- Vite 6.2.0 with hot module replacement
- Tailwind CSS 3.4.1 with custom HSL color system
- Radix Vue 1.9.11 for UI primitives
- Lucide Vue Next 0.468.0 for icons

### Testing & Quality
- Backend: Pest PHP 3.7, PHPStan level 5, Laravel Pint (PSR-12)
- Frontend: Vitest 3.0.9, ESLint + Prettier
- Type coverage requirement: 100% (enforced in Pest)

### Critical Architecture Notes (from Party Mode Analysis)
- **Ziggy Integration**: Use `route()` helper for all URLs, never hardcode. Routes are server-side generated but available client-side.
- **Inertia Lazy Loading**: Always use lazy-loaded components for modals and heavy UI using `defineAsyncComponent`
- **HSL Color System**: Respect semantic color tokens via CSS variables, not arbitrary hex values
- **Radix Vue Primitives**: Located in `/resources/js/components/ui/` - never modify directly, build custom design system on top

## Critical Implementation Rules

### Language-Specific Rules

#### TypeScript
- **Strict mode enabled** - `strict: true` enables ALL strict checks (noImplicitAny, strictNullChecks, strictFunctionTypes)
- **Define interfaces for all Vue component props** before `defineProps<Props>()`
- **Use `@/` alias** for imports within resources/js (resolves to `./resources/js/*`)
- **Import Radix components explicitly**: `import { Button } from '@/components/ui/button'`
- **Route generation uses `route()` from ziggy-js**, never hardcode URLs
- **Lazy load modals**: `const Modal = defineAsyncComponent(() => import('@/components/...'))`
- **isolatedModules: true** means each file must be transpilable independently

#### PHP
- **Every PHP file MUST start with** `declare(strict_types=1);` (line 1, no exceptions)
- **All classes use PSR-4 autoloading** with proper namespacing (`App\...`)
- **Single-action controllers use `__invoke()` method** pattern
- **Type hints required** on all method signatures and return types
- **API controllers return `JsonResponse`** type-hinted responses

#### Vue Components
- **Use `<script setup lang="ts">` format exclusively** - no Options API, no JavaScript-only files
- **Define interfaces for props before `defineProps<Props>()`** - TypeScript needs this order
- **Use Composition API** with composables from `@/composables/`
- **Use `useForm()` from Inertia** for form handling (not native fetch)
- **Barrel exports for UI components** - import from `@/components/ui/button`, not direct paths

#### ESLint Exception Note
The `@typescript-eslint/no-explicit-any: 'off'` rule allows intentional `any` when needed, but TypeScript strict mode still catches unintended `any` types. Use `any` sparingly and with intention.

### Framework-Specific Rules

#### Vue 3 + Composition API
- **Use `<script setup lang="ts">` exclusively** - no Options API
- **Import icons from lucide-vue-next**: `import { IconName } from 'lucide-vue-next'`
- **Use Inertia's Link component** for navigation, never `<a>` tags: `import { Link } from '@inertiajs/vue3'`
- **Form handling via `useForm()` from `@inertiajs/vue3`**:
  ```ts
  const form = useForm({ field: value });
  form.post(route('route.name'), { onSuccess: () => {...} });
  ```
- **Modals use Radix Dialog**: `import { Dialog, DialogContent, ... } from '@/components/ui/dialog'`

#### Laravel + Inertia
- **Single-action controllers** use `__invoke()` method with no suffix (e.g., `RecipeSearchController`, not `Search`)
- **API routes return JSON**: `return response()->json(['data' => $data]);`
- **Use route model binding with slugs** for public URLs
- **Inertia responses use inertia() helper**: `return inertia('Component', ['prop' => $value]);`

#### State Management
- **No global state library** - use Inertia page props + local component state
- **Shared state via composables** in `/resources/js/composables/`
- **Reactive data with `ref()` and `computed()`** from Vue

#### Performance Rules
- **Always lazy load modals** using `defineAsyncComponent()`
- **Use eager loading** for Eloquent relationships to prevent N+1 queries

### Testing Rules

#### Backend Testing (Pest PHP)
- **Use Pest's `test()` function** - NOT PHPUnit's `@test` annotation
- **Use factories for data creation**: `Recipe::factory()->create(['field' => 'value'])`
- **Inertia page assertions**:
  ```php
  $response->assertInertia(function (Assert $page) {
      $page->component('ComponentName')
          ->has('data', 3)
          ->where('data.0.field', 'value');
  });
  ```
- **Feature tests go in** `/tests/Feature/`
- **Unit tests go in** `/tests/Unit/`

#### Frontend Testing (Vitest)
- **Use @testing-library/vue** for component testing
- **Test files use** `.test.ts` or `.spec.ts` extension
- **Coverage reporter** generates reports to `./coverage/js`

#### Coverage Requirements
- **100% type coverage enforced** - Pest will fail if below minimum
- **Run `composer test`** executes full test suite (type coverage, unit, lint, refactor)
- **All tests must pass** before marking development complete

#### Test Data Patterns
- **Use Laravel factories** - don't manually create test data
- **Chain factory methods**: `User::factory()->has(Recipe::factory()->count(3))->create()`

### Code Quality & Style Rules

#### Linting/Formatting
- **Run `composer lint`** to fix all style issues before committing
- **Prettier config**: semi-colons, single quotes, 150 char width, 4-space tabs
- **ESLint ignores**: `vendor`, `node_modules`, `public`, UI components (`resources/js/components/ui/*`)
- **Pint preset**: PSR-12 for PHP

#### File Organization
- **UI components**: `/resources/js/components/ui/` - Radix primitives (never modify)
- **Feature components**: `/resources/js/components/{FeatureName}/`
- **Pages**: `/resources/js/pages/`
- **Composables**: `/resources/js/composables/`
- **Types**: `/resources/js/types/`

#### Naming Conventions
- **PHP classes**: PascalCase (`RecipeSearchController`)
- **PHP methods**: camelCase (`getUserRecipes`)
- **PHP variables**: camelCase (`$recipeId`)
- **Vue components**: PascalCase (`RecipeCard.vue`)
- **Vue props/methods**: camelCase
- **Component files**: PascalCase (`RecipeCard.vue`)
- **Other files**: kebab-case (`recipe-search-modal.vue` if not a component)

#### Documentation
- **PHPDoc recommended** for PHP methods (especially public APIs)
- **TypeScript types preferred** over JSDoc comments
- **No excessive comments** - let the code speak

### Development Workflow Rules

#### Git Conventions
- **Main branch**: `main` - all PRs target this branch
- **Feature branches**: Use Conventional Branch naming (feature/, bugfix/, hotfix/, chore/, etc)
- **Commit messages**: Conventional Commits preferred, shorter messages ok for small changes
- **Co-author attribution**: Include when AI-assisted: "Co-Authored-By: ..."

#### Local Development
- **Run `composer dev`** to start all services (server, queue, logs, vite)
- **Uses concurrently** to run services in parallel with color-coded output
- **Docker Compose** available for containerized development
- **Sail** option for Docker-based Laravel environment

#### Code Quality Gates
- **`composer test`** must pass before considering work complete
- **Includes**: type coverage, unit tests, linting, refactor checks
- **Manual QA** recommended for feature verification

### Critical Don't-Miss Rules

#### Anti-Patterns to Avoid
- **NEVER hardcode URLs** - always use `route()` helper from ziggy-js
- **NEVER use Options API** - Composition API with `<script setup>` only
- **NEVER skip `declare(strict_types=1)`** in PHP files - must be line 1
- **NEVER use native fetch for forms** - use Inertia's `useForm()` for form submissions
- **NEVER modify Radix UI components** directly in `resources/js/components/ui/`
- **NEVER use `any` type indiscriminately** - TypeScript strict mode catches unintended any

#### Edge Cases to Handle
- **Nullable numeric values**: Ingredient amounts can be 0 or null - handle both cases
- **Optimistic UI updates**: For favorite toggles, update UI immediately then sync with server
- **Date filtering**: Meal plans should filter out ended plans when displaying for selection
- **Empty collections**: Handle gracefully when users have no collections/meal plans

#### Security Rules
- **Always verify user ownership** before allowing modifications (use Laravel policies)
- **Never trust client-side data** - always validate on server
- **Use Laravel's built-in auth** - don't roll your own authentication

#### Performance Gotchas
- **Always lazy-load modals** with `defineAsyncComponent()` - don't bundle everything
- **Always eager-load relationships** to prevent N+1 queries
- **Use select() on queries** to limit columns retrieved

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

Last Updated: 2026-03-03
