# Architecture Documentation - NutriPlan

## Executive Summary

NutriPlan is a **monolithic full-stack web application** built with Laravel 12 and Vue 3, connected via Inertia.js. This architecture provides a SPA-like user experience while maintaining server-side routing and traditional MVC patterns.

**Key Characteristics:**
- Single codebase (monolith)
- Server-side routing with Inertia.js
- Vue components for UI rendering
- Laravel MVC with Service layer
- Eloquent ORM for database operations

---

## Technology Stack

### Backend

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Laravel | 12.0 |
| Language | PHP | 8.2+ |
| ORM | Eloquent | - |
| Authentication | Laravel Sanctum | 4.0 |
| Queue | Database Queue | - |
| Broadcasting | Pusher | - |

### Frontend

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Vue.js | 3.5.13 |
| Language | TypeScript | 5.2.2 |
| Build Tool | Vite | 6.2.0 |
| CSS Framework | Tailwind CSS | 3.4.1 |
| UI Components | Radix Vue | 1.9.11 |
| Icons | Lucide Vue Next | 0.468.0 |

### Full-stack Bridge

| Component | Technology | Purpose |
|-----------|-----------|---------|
| Bridge | Inertia.js | 2.0 |
| Route Sharing | Ziggy | 2.4.2 |

---

## Architecture Pattern

### Inertia.js Monolith

```
┌─────────────────────────────────────────────────────────┐
│                    Browser                              │
│  ┌───────────────────────────────────────────────────┐  │
│  │              Vue Components                        │  │
│  │  - Pages (Inertia)                                │  │
│  │  - Components (Reusable)                          │  │
│  │  - Composables (Logic)                            │  │
│  └───────────────────────────────────────────────────┘  │
│                          ↑                               │
│                          │ Inertia Protocol              │
│                          ↓                               │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│                   Laravel Backend                       │
│  ┌───────────────────────────────────────────────────┐  │
│  │              Router (web.php)                      │  │
│  │                    ↓                               │  │
│  │              Controllers                            │  │
│  │                    ↓                               │  │
│  │     ┌──────────────────────────────┐              │  │
│  │     │  Service Layer (Business)    │              │  │
│  │     └──────────────────────────────┘              │  │
│  │                    ↓                               │  │
│  │     ┌──────────────────────────────┐              │  │
│  │     │  Models (Eloquent ORM)       │              │  │
│  │     └──────────────────────────────┘              │  │
│  └───────────────────────────────────────────────────┘  │
│                           ↓                               │
│  ┌───────────────────────────────────────────────────┐  │
│  │              Database (SQLite/MySQL/PostgreSQL)    │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

**Benefits:**
- No separate API frontend/backend
- Server-side routing with SEO benefits
- SPA-like UX without client-side routing complexity
- Shared TypeScript/PHP types through Ziggy

---

## Layer Architecture

### Presentation Layer

**Frontend (Vue 3 + TypeScript):**
- **Pages:** Inertia page components (`resources/js/pages/`)
- **Components:** Reusable UI components (`resources/js/components/`)
- **Composables:** Shared logic via Composition API
- **Layouts:** Page layout templates

**Styling:**
- Tailwind CSS for utility-first styling
- Radix Vue for accessible component primitives
- Lucide for icons

### Application Layer

**Controllers (`app/Http/Controllers/`):**
- Handle HTTP requests
- Validate input via Form Requests
- Delegate to Services for business logic
- Return Inertia responses or JSON

**Services (`app/Services/`):**
- Encapsulate business logic
- Coordinate between models and external services
- Handle complex operations (e.g., recipe parsing, meal planning)

**Actions (`app/Actions/`):**
- Single-purpose, reusable operations
- Used for simple business operations

### Domain Layer

**Models (`app/Models/`):**
- Eloquent ORM models
- Define relationships
- Encapsulate data access

**Value Objects (`app/ValueObjects/`):**
- Immutable data structures (e.g., `Measurement`)

**Policies (`app/Policies/`):**
- Authorization logic

### Infrastructure Layer

**External Services:**
- OpenAI API - Recipe import
- Barcode API - Product lookup
- Pusher - Real-time broadcasting

---

## Data Architecture

### Database Design

**Database:** SQLite (default), supports MySQL/PostgreSQL

**Key Entities:**
- Users (authentication, ownership)
- Recipes (core entity)
- Categories (recipe organization)
- Collections (user-defined grouping)
- Ingredients (normalized ingredient data)
- Meal Plans (time-based meal planning)
- Shopping Lists (derived from meal plans)

**Relationships:**
- Many-to-Many: Recipes ↔ Categories, Recipes ↔ Collections
- Many-to-Many with Pivot: Recipes ↔ Ingredients (RecipeIngredient)
- One-to-Many: User → Recipes, MealPlan → Days, etc.

---

## API Design

### Routing Strategy

1. **Web Routes (Inertia):**
   - Server-side rendering with Vue components
   - Traditional RESTful resource routes
   - Authentication via session

2. **API Routes:**
   - JSON responses for external integrations
   - Token authentication via Sanctum
   - Browser extensions, mobile apps

### Route Organization

```
routes/
├── web.php           # Main application routes (Inertia)
├── api.php           # JSON API routes
├── auth.php          # Authentication routes
├── settings.php      # User settings routes
├── channels.php      # Broadcasting channels
└── console.php       # CLI commands
```

---

## State Management

### Frontend State

- **Local State:** Vue `ref`/`reactive` for component state
- **Server State:** Fetched via Ziggy routes, cached by Inertia
- **Global State:** Provide/inject for theme, user preferences

### Backend State

- **Session:** Laravel session for authenticated user
- **Cache:** Redis/file cache for expensive operations
- **Queue:** Database queue for async jobs

---

## Security Architecture

### Authentication

- **Web:** Session-based (Laravel default)
- **API:** Token-based (Laravel Sanctum)
- **Passwords:** Hashed via Laravel's bcrypt

### Authorization

- **Policies:** Gate logic for user actions
- **Middleware:** Route protection (`auth:sanctum`, `verified`)

### Data Protection

- **CSRF:** Laravel CSRF tokens for forms
- **XSS:** Escaping via Blade/Vue
- **SQL Injection:** Eloquent parameter binding

---

## Performance Considerations

### Optimization Strategies

1. **Frontend:**
   - Vite for fast builds and HMR
   - Component code splitting
   - Image optimization

2. **Backend:**
   - Eager loading for relationships
   - Query caching
   - Queue for long-running tasks

3. **Database:**
   - Indexed columns (slugs, foreign keys)
   - Proper relationship definitions
   - Migration-based schema management

---

## Scalability

### Current Limitations (Monolith)

- Single server deployment
- Shared resources
- Limited horizontal scaling

### Scaling Options

1. **Vertical Scaling:**
   - More powerful server
   - More queue workers
   - Database read replicas

2. **Service Extraction:**
   - Separate queue workers
   - External cache (Redis)
   - CDN for assets

3. **Future: Microservices (if needed)**
   - Extract recipe parsing service
   - Separate barcode service
   - Auth as a service

---

## Integration Points

### External Services

1. **OpenAI API**
   - Recipe import parsing
   - Ingredient normalization
   - Instruction formatting

2. **Barcode API**
   - Product lookup
   - Shopping list item creation

3. **Pusher**
   - Real-time notifications
   - Live updates (planned)

### Frontend-Backend Integration

- **Ziggy:** Laravel routes available in JavaScript
- **Inertia:** Data passed as props to Vue components
- **Shared Props:** User data, errors, flash messages via middleware

---

## Development Workflow

### Code Organization

```
Development Path:
1. Create migration → `database/migrations/`
2. Create model → `app/Models/`
3. Create controller → `app/Http/Controllers/`
4. Add route → `routes/web.php` or `routes/api.php`
5. Create page → `resources/js/pages/`
6. Create components → `resources/js/components/`
7. Test → `tests/Feature/` or `tests/js/`
```

### Testing Strategy

- **PHP:** Pest for feature and unit tests
- **JavaScript:** Vitest for component tests
- **Coverage:** Type coverage (100% target)
- **Quality:** PHPStan, Pint, Rector

---

## Deployment Architecture

### Development

```
Local Machine:
- Laravel Sail (Docker)
- `composer dev` runs all services
- SQLite database
```

### Production

```
Single Server:
- Nginx → PHP-FPM → Laravel
- MySQL/PostgreSQL database
- Supervisor (queue workers)
- Redis (cache, optional)
- Let's Encrypt (SSL)
```

### CI/CD

- **Lint/Tests:** GitHub Actions
- **Deployment:** Manual or Forge-compatible
- **Monitoring:** Logs, error tracking

---

## Known Limitations

1. **No frontend state management library** (Vuex/Pinia) - uses local state
2. **No dedicated cache layer** in default setup
3. **Database-backed queue** (Redis recommended for production)
4. **No API rate limiting** configured
5. **Limited real-time features** (Pusher configured but not fully utilized)

---

## Future Considerations

1. **API Versioning** if expanding external API
2. **GraphQL** alternative to REST
3. **Event Sourcing** for recipe import tracking
4. **Read Replicas** for scaling reads
5. **CDN** for static assets
6. **Microservice extraction** for specific features
