# Source Tree Analysis - NutriPlan

This document provides a complete annotated directory tree of the NutriPlan project.

## Project Root

```
NutriPlan/
├── app/                          # Laravel application code
│   ├── Actions/                  # Domain actions (single-purpose classes)
│   │   ├── AddRecipeToCollectionAction.php
│   │   ├── CreateCollectionAction.php
│   │   ├── DeleteRecipeAction.php
│   │   └── FetchRecipe.php
│   ├── Concerns/                 # Shared traits
│   ├── Console/                  # Artisan commands
│   │   └── Commands/
│   │       └── ReimportRecipesCommand.php
│   ├── Enums/                    # PHP enums
│   │   └── MeasurementUnit.php
│   ├── Events/                   # Domain events
│   │   └── RecipeImportCompleted.php
│   ├── Exceptions/               # Exception classes
│   │   └── RecipeImport/
│   ├── Http/                     # HTTP layer
│   │   ├── Controllers/          # Route controllers
│   │   │   ├── Api/              # API endpoints
│   │   │   │   ├── BarcodeLookupController.php
│   │   │   │   ├── ItemSearchController.php
│   │   │   │   ├── RecipeImportController.php
│   │   │   │   └── RecipeSearchController.php
│   │   │   ├── Auth/             # Authentication controllers
│   │   │   ├── Settings/         # User settings controllers
│   │   │   ├── CategoryController.php
│   │   │   ├── CollectionController.php
│   │   │   ├── FavoriteController.php
│   │   │   ├── IngredientController.php
│   │   │   ├── MealAssignmentController.php
│   │   │   ├── MealPlanController.php
│   │   │   ├── RecipeController.php
│   │   │   ├── ShoppingListController.php
│   │   │   └── UserRecipeController.php
│   │   ├── Middleware/           # HTTP middleware
│   │   │   ├── HandleAppearance.php
│   │   │   └── HandleInertiaRequests.php
│   │   ├── Requests/             # Form request validation
│   │   ├── Resources/            # API resources (transformers)
│   │   └── Kernel.php            # HTTP kernel
│   ├── Jobs/                     # Queue jobs
│   │   └── ImportRecipeJob.php
│   ├── Models/                   # Eloquent models (14 models)
│   │   ├── Category.php
│   │   ├── Collection.php
│   │   ├── Ingredient.php
│   │   ├── MealAssignment.php
│   │   ├── MealPlan.php
│   │   ├── MealPlanDay.php
│   │   ├── MealPlanRecipe.php
│   │   ├── NutritionInformation.php
│   │   ├── Recipe.php
│   │   ├── RecipeIngredient.php
│   │   ├── ShoppingList.php
│   │   ├── ShoppingListItem.php
│   │   └── User.php
│   ├── Policies/                 # Authorization policies
│   │   ├── CollectionPolicy.php
│   │   ├── MealPlanPolicy.php
│   │   ├── RecipePolicy.php
│   │   └── ShoppingListPolicy.php
│   ├── Providers/                # Service providers
│   │   └── AppServiceProvider.php
│   ├── Services/                 # Business logic services
│   │   ├── BarcodeService.php
│   │   ├── IngredientNormalizationService.php
│   │   ├── IngredientParser.php
│   │   ├── InstructionNormalizationService.php
│   │   ├── MealPlanCopyService.php
│   │   ├── NutritionParser.php
│   │   ├── RecipeParser.php
│   │   └── ShoppingListService.php
│   └── ValueObjects/             # Value objects
│       └── Measurement.php
├── bootstrap/                    # Bootstrap files
│   └── app.php                   # Application bootstrap
├── config/                       # Configuration files
│   ├── app.php                   # App configuration
│   ├── auth.php                  # Authentication settings
│   ├── broadcasting.php          # Broadcast configuration
│   ├── cache.php                 # Cache configuration
│   ├── database.php              # Database configuration
│   ├── filesystems.php           # Filesystem configuration
│   ├── inertia.php               # Inertia.js configuration
│   ├── logging.php               # Logging configuration
│   ├── mail.php                  # Mail configuration
│   ├── queue.php                 # Queue configuration
│   ├── recipe.php                # Recipe-specific configuration
│   ├── sanctum.php               # Sanctum configuration
│   ├── services.php              # External services configuration
│   └── session.php               # Session configuration
├── database/                     # Database layer
│   ├── factories/                # Model factories
│   │   ├── CategoryFactory.php
│   │   ├── CollectionFactory.php
│   │   ├── IngredientFactory.php
│   │   ├── MealAssignmentFactory.php
│   │   ├── MealPlanDayFactory.php
│   │   ├── MealPlanFactory.php
│   │   ├── NutritionInformationFactory.php
│   │   ├── RecipeFactory.php
│   │   ├── ShoppingListItemFactory.php
│   │   ├── ShoppingListFactory.php
│   │   └── UserFactory.php
│   ├── migrations/               # Database migrations (31 files)
│   └── seeders/                  # Database seeders
│       ├── DatabaseSeeder.php
│       └── RecipeSeeder.php
├── public/                       # Public web root
│   └── ...
├── resources/                    # Frontend resources
│   ├── css/                      # Stylesheets
│   │   └── app.css
│   ├── js/                       # Vue.js application
│   │   ├── app.ts                # Application entry point
│   │   ├── components/           # Vue components (40+ components)
│   │   ├── composables/          # Composition functions
│   │   ├── layouts/              # Vue layouts
│   │   ├── pages/                # Inertia page components
│   │   │   ├── auth/
│   │   │   ├── Categories/
│   │   │   ├── Collections/
│   │   │   ├── Dashboard.vue
│   │   │   ├── Landing.vue
│   │   │   ├── MealPlans/
│   │   │   ├── Recipes/
│   │   │   ├── settings/
│   │   │   └── ShoppingLists/
│   │   ├── plugins/              # Vue plugins
│   │   ├── types/                # TypeScript type definitions
│   │   └── utils/                # Utility functions
│   └── views/                    # Blade templates (minimal)
├── routes/                       # Route definitions
│   ├── api.php                   # API routes
│   ├── auth.php                  # Authentication routes
│   ├── channels.php              # Broadcast channels
│   ├── console.php               # Console routes
│   ├── settings.php              # Settings routes
│   └── web.php                   # Web routes (Inertia)
├── storage/                      # Storage directory
│   ├── app/                      # Application files
│   ├── framework/                # Framework files
│   └── logs/                     # Log files
├── tests/                        # Test files
│   ├── Feature/                  # Feature tests (Pest)
│   ├── Unit/                     # Unit tests (Pest)
│   └── js/                       # JavaScript tests (Vitest)
├── vendor/                       # Composer dependencies
├── node_modules/                 # NPM dependencies
├── _bmad/                        # BMAD method files
├── _bmad-output/                 # BMAD output
├── docs/                         # Project documentation (this folder)
├── documentation/                # Auto-generated code docs
├── specs/                        # Feature specifications (46 files)
│
├── .env.example                  # Environment variables template
├── .gitignore                    # Git ignore rules
├── artisan                       # Laravel CLI
├── composer.json                 # PHP dependencies
├── docker-compose.yml            # Docker configuration (Sail)
├── package.json                  # NPM dependencies
├── phpunit.xml                   # PHPUnit configuration
├── phpstan.neon                  # PHPStan configuration
├── pint.json                     # Pint configuration
├── rector.php                    # Rector configuration
├── tailwind.config.js            # Tailwind CSS configuration
├── tsconfig.json                 # TypeScript configuration
├── vite.config.ts                # Vite configuration
├── vitest.config.ts              # Vitest configuration
├── README.md                     # Project readme
├── SPECS.md                      # Project specifications
├── todo.md                       # TODO list
└── dashboard-ideas.md            # Dashboard feature ideas
```

## Critical Directories Explained

### app/ - Backend Application Code
This is the core of the Laravel backend containing all business logic:
- **Models/** - Database models with relationships
- **Http/Controllers/** - Request handlers organized by feature
- **Services/** - Business logic services (parsing, normalization, etc.)
- **Jobs/** - Queue jobs for async processing
- **Policies/** - Authorization logic

### resources/js/ - Frontend Application
Vue.js application with TypeScript:
- **pages/** - Inertia page components (route handlers)
- **components/** - Reusable Vue components
- **composables/** - Vue composition functions
- **layouts/** - Page layout components

### routes/ - Route Definitions
- **web.php** - Main Inertia.js routes
- **api.php** - API endpoints
- **auth.php** - Authentication routes

### database/migrations/ - Database Schema
31 migration files defining the complete database schema.

### specs/ - Feature Specifications
46 detailed feature specification documents covering all planned features.

## Entry Points

1. **Backend Entry:** `bootstrap/app.php`
2. **Frontend Entry:** `resources/js/app.ts`
3. **SSR Entry:** `resources/js/ssr.ts`
4. **CLI Entry:** `artisan`
5. **Web Server:** Laravel's `public/index.php`

## Integration Points

### Laravel ↔ Vue Integration
- **Middleware:** `HandleInertiaRequests` - Shared props to frontend
- **Plugin:** Ziggy - Laravel routes available in JavaScript
- **Bridge:** Inertia.js - Server-side routing with Vue rendering

### External Services
- **OpenAI API** - Recipe import and ingredient parsing
- **Barcode API** - Product lookup via FreeWebAPI
- **Pusher** - Real-time broadcasting (Laravel Echo)
