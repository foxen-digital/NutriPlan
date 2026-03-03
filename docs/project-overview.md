# NutriPlan Project Overview

## Project Name and Purpose

NutriPlan is a modern recipe management and meal planning application built with Laravel, Vue.js, and Inertia.js. It allows users to create, import, and organize recipes in custom collections, plan their meals with an intuitive calendar system, and generate smart shopping lists to streamline grocery shopping. With AI-powered ingredient parsing and community recipe sharing, NutriPlan makes meal planning effortless.

**Note:** This application has been developed as an experiment in using AI IDEs to build web applications. Code quality may not be up to production standards, and bugs/vulnerabilities may exist. Use at your own risk. Not intended for production use.

## Executive Summary

NutriPlan is a monolithic full-stack web application combining:
- **Backend:** Laravel 12 (PHP 8.2+) with Eloquent ORM
- **Frontend:** Vue 3 with TypeScript and Inertia.js for SPA-like experience
- **Styling:** Tailwind CSS with Radix Vue components
- **Database:** SQLite (supports MySQL/PostgreSQL)
- **Testing:** Pest (PHP) and Vitest (JavaScript)

The application features:
- Recipe management with AI-powered import from websites
- Custom categories and collections for organization
- Meal planning with calendar-based scheduling
- Smart shopping list generation
- Barcode scanning for quick item addition
- Community recipe sharing

## Tech Stack Summary Table

| Category | Technology | Version | Purpose |
|----------|-----------|---------|---------|
| Backend Framework | Laravel | 12.0 | PHP application framework |
| PHP Runtime | PHP | 8.2+ | Server-side language |
| Frontend Framework | Vue.js | 3.5.13 | Progressive UI framework |
| TypeScript | TypeScript | 5.2.2 | Type-safe JavaScript |
| Full-stack Bridge | Inertia.js | 2.0 | Connect Laravel with Vue |
| Build Tool | Vite | 6.2.0 | Fast module bundler |
| CSS Framework | Tailwind CSS | 3.4.1 | Utility-first styling |
| UI Components | Radix Vue | 1.9.11 | Accessible component primitives |
| Icons | Lucide Vue Next | 0.468.0 | Icon library |
| Authentication | Laravel Sanctum | 4.0 | API/token authentication |
| Testing (PHP) | Pest | 3.7+ | Elegant testing framework |
| Testing (JS) | Vitest | 3.0.9 | Fast unit testing |
| Database | SQLite | - | Default database |

## Architecture Type Classification

**Repository Type:** Monolith (single cohesive codebase)

**Architecture Pattern:** Inertia.js Monolith
- Server-side routing with Laravel
- Vue components rendered via Inertia.js
- SPA-like user experience without separate API
- Traditional Laravel MVC with Service layer
- Eloquent ORM for database operations

## Repository Structure

```
NutriPlan/
├── app/                    # Laravel application code
│   ├── Actions/           # Domain actions
│   ├── Http/              # Controllers, Middleware, Requests
│   ├── Models/            # Eloquent models (14 models)
│   ├── Services/          # Business logic services
│   └── ValueObjects/      # Value objects
├── resources/js/          # Vue.js frontend
│   ├── components/        # Reusable Vue components
│   ├── composables/       # Vue composition functions
│   ├── pages/             # Inertia page components
│   └── layouts/           # Vue layout components
├── routes/                # Route definitions
│   ├── web.php           # Web routes (Inertia)
│   ├── api.php           # API routes
│   └── auth.php          # Authentication routes
├── database/              # Database migrations and factories
├── config/                # Configuration files
├── tests/                 # Pest and Vitest tests
├── specs/                 # Feature specifications (46 files)
├── documentation/         # Auto-generated code documentation
└── public/                # Public assets
```

## Links to Detailed Documentation

- [Architecture Documentation](./architecture.md)
- [API Contracts](./api-contracts.md)
- [Data Models](./data-models.md)
- [Source Tree Analysis](./source-tree-analysis.md)
- [Component Inventory](./component-inventory.md)
- [Development Guide](./development-guide.md)
- [Deployment Guide](./deployment-guide.md)

## Existing Documentation

- [README.md](../README.md) - Main project documentation with setup instructions
- [SPECS.md](../SPECS.md) - Project specifications
- [specs/](../specs/) - 46 detailed feature specification files
- [documentation/](../documentation/index.md) - Comprehensive auto-generated code documentation
