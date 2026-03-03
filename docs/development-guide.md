# Development Guide - NutriPlan

This guide covers setting up and working with the NutriPlan codebase.

## Prerequisites

- **PHP:** 8.2 or higher
- **Node.js:** 18.x or higher
- **Composer:** 2.x
- **Database:** SQLite (default), MySQL, or PostgreSQL

### External Services (Optional but Recommended)

1. **RapidAPI Account** - For barcode lookup
   - Signup: https://freewebapi.com/data-apis/barcode-lookup-api/

2. **OpenAI Account** - For AI-powered recipe import
   - Or any OpenAI-compatible LLM service

---

## Environment Setup

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/nutriplan.git
cd nutriplan
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure External Services

Edit `.env` file:

```env
# Barcode Lookup Service
BARCODE_API_KEY=your_barcode_api_key_here

# OpenAI (or compatible) for recipe parsing
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_API_BASE=https://api.openai.com/v1  # Or your compatible endpoint
```

### 6. Create Database

```bash
# SQLite (default)
touch database/database.sqlite

# Or configure MySQL/PostgreSQL in .env
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. Seed Database (Optional)

```bash
php artisan db:seed
```

---

## Running the Application

### Development Server (All Services)

```bash
composer dev
```

This runs:
- Laravel development server (port 80)
- Queue listener
- Log viewer (Pail)
- Vite development server (port 5173)

**Application:** http://localhost

### Individual Services

```bash
# Laravel server only
php artisan serve

# Vite dev server only
npm run dev

# Queue worker
php artisan queue:listen --tries=1

# Log viewer
php artisan pail --timeout=0
```

### SSR Mode

```bash
composer dev:ssr
```

Replaces Vite with Inertia SSR server.

---

## Build Commands

### Frontend Build

```bash
# Production build
npm run build

# SSR build
npm run build:ssr
```

### Optimizing for Production

```bash
# Optimize Laravel
php artisan optimize

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache
```

---

## Testing

### Run All Tests

```bash
composer test
```

This runs:
- Type coverage tests
- Unit tests
- Linting (Pint + ESLint/Prettier)
- Refactoring checks (Rector)

### Individual Test Suites

```bash
# PHP Tests (Pest)
composer test:unit

# PHP Static Analysis
composer test:types

# JavaScript Tests
npm run test

# JavaScript Coverage
npm run test:coverage
```

### Watch Mode

```bash
# PHP tests
pest --watch

# JavaScript tests
npm run test:watch
```

---

## Code Quality

### Linting

```bash
# Auto-fix all
composer lint

# Check only (no fixes)
composer test:lint
```

### Static Analysis

```bash
# PHPStan analysis
composer test:types
```

### Refactoring

```bash
# Check refactoring suggestions
composer test:refactor

# Apply refactorings
composer refactor
```

---

## Development Workflow

### Making Changes

1. **Backend Changes (PHP)**
   - Edit files in `app/`
   - Run `pint` to fix code style
   - Write tests in `tests/`
   - Run `pest` to verify

2. **Frontend Changes (Vue/TS)**
   - Edit files in `resources/js/`
   - Vite HMR will auto-refresh
   - Write tests in `tests/js/`
   - Run `vitest` to verify

3. **Database Changes**
   - Create migration: `php artisan make:migration`
   - Edit migration file in `database/migrations/`
   - Run migration: `php artisan migrate`
   - Update models in `app/Models/`

### Adding Routes

1. **Web Routes (Inertia pages)**
   - Add to `routes/web.php`
   - Create Vue page in `resources/js/pages/`
   - Optional: Create controller in `app/Http/Controllers/`

2. **API Routes**
   - Add to `routes/api.php`
   - Create controller in `app/Http/Controllers/Api/`
   - Add validation request in `app/Http/Requests/`

### Adding Components

1. **Reusable UI Components**
   - Add to `resources/js/components/ui/`
   - Follow Radix Vue patterns
   - Use TypeScript for props

2. **Feature Components**
   - Add to `resources/js/components/[FeatureName]/`
   - Create composable for shared logic
   - Use TypeScript for type safety

---

## File Upload / Assets

### Public Assets

Place in `public/` directory:
- Images: `public/images/`
- Static files: `public/`

### Component Assets

Import in Vue components:
```typescript
import myImage from '@/assets/image.png'
```

---

## Debugging

### Laravel Debugging

```bash
# Show routes
php artisan route:list

# Show config
php artisan config:show

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Tinker (REPL)
php artisan tinker
```

### Frontend Debugging

```bash
# Check Vite build
npm run build -- --debug

# Analyze bundle
npm run build -- --mode analyze
```

### Logging

- **Laravel Logs:** `storage/logs/laravel.log`
- **Pail:** Real-time log viewer (`php artisan pail`)

---

## Composer Scripts Reference

| Command | Description |
|---------|-------------|
| `composer dev` | Start all dev services |
| `composer dev:ssr` | Start with SSR |
| `composer lint` | Fix code style |
| `composer test` | Run all tests |
| `composer test:types` | PHPStan analysis |
| `composer test:unit` | Pest unit tests |
| `composer test:refactor` | Rector dry-run |
| `composer refactor` | Apply Rector rules |

---

## NPM Scripts Reference

| Command | Description |
|---------|-------------|
| `npm run dev` | Start Vite dev server |
| `npm run build` | Production build |
| `npm run build:ssr` | SSR build |
| `npm run test` | Run Vitest |
| `npm run test:watch` | Watch mode |
| `npm run test:coverage` | Coverage report |
| `npm run lint` | ESLint fix |
| `npm run format` | Prettier format |
| `npm run format:check` | Check formatting |

---

## Troubleshooting

### Common Issues

**1. Composer Install Fails**
```bash
composer install --ignore-platform-reqs
```

**2. NPM Install Fails**
```bash
npm install --legacy-peer-deps
```

**3. Migration Errors**
```bash
php artisan migrate:fresh
```

**4. Vite HMR Not Working**
```bash
rm -rf node_modules/.vite
npm run dev
```

**5. Permission Issues**
```bash
chmod -R 775 storage bootstrap/cache
```

---

## IDE Recommendations

### VS Code Extensions
- **PHP Intelephense** - PHP IntelliSense
- **Laravel Extra Intellisense** - Laravel specific
- **Vue Language Features (Volar)** - Vue 3 support
- **TypeScript Vue Plugin (Volar)** - TS in Vue
- **Tailwind CSS IntelliSense** - Tailwind classes
- **ESLint** - JavaScript linting
- **Prettier** - Code formatting

### PhpStorm
- Built-in Laravel support
- Vue.js plugin
- TypeScript support
- Built-in Pint integration
