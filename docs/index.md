# NutriPlan Documentation Index

## Project Overview

- **Type:** Monolith (single cohesive codebase)
- **Primary Language:** PHP 8.2+ with Vue 3 (TypeScript)
- **Architecture:** Inertia.js Monolith - Server-side routing with Vue components, SPA-like UX

## Quick Reference

### Tech Stack

| Category | Technology | Version |
|----------|-----------|---------|
| Backend | Laravel | 12.0 |
| Frontend | Vue 3 + TypeScript | 3.5.13 / 5.2.2 |
| Full-stack | Inertia.js | 2.0 |
| Styling | Tailwind CSS + Radix Vue | 3.4.1 / 1.9.11 |
| Database | SQLite (MySQL/PostgreSQL supported) | - |
| Testing | Pest / Vitest | 3.7+ / 3.0.9 |

### Entry Points

- **Web:** http://localhost (development)
- **Backend:** `bootstrap/app.php`
- **Frontend:** `resources/js/app.ts`
- **CLI:** `artisan`

### Architecture Pattern

**Inertia.js Monolith** - Traditional Laravel MVC with Vue 3 components rendered server-side via Inertia.js, providing SPA-like experience without separate API frontend.

---

## Generated Documentation

### Core Documentation

- [Project Overview](./project-overview.md) - Project summary and links
- [Architecture](./architecture.md) - System architecture and design decisions
- [API Contracts](./api-contracts.md) - All API endpoints and routes
- [Data Models](./data-models.md) - Database schema and model relationships
- [Source Tree Analysis](./source-tree-analysis.md) - Complete directory structure
- [Component Inventory](./component-inventory.md) - Vue component catalog
- [Development Guide](./development-guide.md) - Setup and development workflow
- [Deployment Guide](./deployment-guide.md) - Production deployment

---

## Existing Documentation

### Project Root

- [README.md](../README.md) - Main project documentation with setup instructions
- [SPECS.md](../SPECS.md) - Project specifications
- [specs/](../specs/) - 46 detailed feature specification files covering all planned features

### Auto-Generated Code Documentation

- [documentation/index.md](../documentation/index.md) - Comprehensive auto-generated code documentation for all app files, config, migrations, and routes

---

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Node.js 18.x or higher
- Composer 2.x
- SQLite, MySQL, or PostgreSQL

### Quick Start

```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
touch database/database.sqlite
php artisan migrate

# Start development
composer dev
```

**Application:** http://localhost

### Development Commands

```bash
# Start all services (server, queue, vite)
composer dev

# Run tests
composer test

# Fix code style
composer lint

# Build for production
npm run build
```

---

## Feature Areas

### Recipe Management
- Create, edit, delete recipes
- Import from websites (AI-powered)
- Organize with categories and collections
- Nutritional information tracking

### Meal Planning
- Calendar-based meal planning
- Recipe scaling and servings
- Meal assignments per day
- Copy existing plans

### Shopping Lists
- Auto-generate from meal plans
- Manual list creation
- Barcode scanning (mobile)
- Purchase tracking

### User Features
- Favorites
- Community recipes (public/private)
- Custom collections
- API token management

---

## External Services

- **OpenAI API** - Recipe import and ingredient parsing
- **Barcode API** - Product lookup via FreeWebAPI
- **Pusher** - Real-time broadcasting (optional)

---

## Project Stats

- **Models:** 14 Eloquent models
- **Migrations:** 31 database migrations
- **Controllers:** 30+ controllers
- **Components:** 65+ Vue components
- **Spec Files:** 46 feature specifications
- **Test Suites:** Pest (PHP) and Vitest (JavaScript)

---

## Development Resources

### Code Quality

- **Static Analysis:** PHPStan
- **Testing:** Pest (PHP), Vitest (JS)
- **Linting:** Laravel Pint, ESLint
- **Formatting:** Prettier

### Documentation Tools

- **Laravel:** [laravel.com](https://laravel.com)
- **Vue.js:** [vuejs.org](https://vuejs.org)
- **Inertia.js:** [inertiajs.com](https://inertiajs.com)
- **Tailwind CSS:** [tailwindcss.com](https://tailwindcss.com)

---

## Notes

**IMPORTANT:** This application was developed as an experiment in using AI IDEs to build web applications. Code quality may not be up to production standards, and bugs/vulnerabilities may exist. Use at your own risk. Not intended for production use.
