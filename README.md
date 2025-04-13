# NutriPlan

NutriPlan is a modern recipe management and meal planning application built with Laravel, Vue.js, and Inertia.js. It allows users to collect, organize, and plan their recipes efficiently.

## IMPORTANT

The application has been developed as an experiment in using AI IDEs to build web applications.  Code quality may not be up to production standards, and bugs / vulnerabilities may exist.  Use at your own risk.

Not intended for production use.

## Features

### Discover & Organize Your Recipes:
- Effortless Recipe Management: Easily create, view, edit, and manage all your recipes in one central location.
- Import from Anywhere*: Seamlessly import recipes from your favorite websites with automatic parsing, utilizing AI for clean & precise ingredient parsing.
- Smart Organization: Use categories and custom collections to keep your recipe library tidy and easy to navigate.
- Flexible Recipe Details: Scale recipes for different serving sizes, manage ingredients precisely, and utilize optional unit measurements.
- Favorites: Quickly access your most-loved recipes by marking them as favorites.
- Control Your Privacy: Choose whether to keep your recipes private or share them with the community.
- Explore Community Recipes: Discover recipes shared by other users and browse their creations.

<sup>*Not literally anywhere</sup>

### Plan Your Meals with Ease:
- Intuitive Meal Planning: Create detailed weekly or monthly meal plans tailored to your needs.
- Recipe Assignment: Easily add recipes to your meal plans and specify servings.
- Serving & Meal Tracking: Manage servings for each recipe within your plan and track available meals.
- Day-Based Structure: Organize your meal plan clearly by day for a better overview.
- Meal Assignments: Assign specific recipe servings to particular days within your plan.
- Cooking Reminders: Flag meals that need cooking to stay on top of your prep schedule.
- Duplicate Plans: Save time by easily copying existing meal plans to create new ones.

### Streamline Your Shopping:
- Automatic Shopping Lists: Generate comprehensive shopping lists directly from your meal plans in just one click.
- Manual List Creation: Create and manage custom shopping lists for any occasion.
- Purchase Tracking: Keep track of items you've already bought.
- Barcode Scanning (Mobile): Quickly add items to your shopping list by scanning their barcodes with your mobile device.

## Tech Stack

- **Backend Framework**: Laravel 10.x
- **Frontend Framework**: Vue.js 3.x with TypeScript
- **Full-stack Framework**: Inertia.js
- **CSS Framework**: Tailwind CSS
- **Testing**: Pest PHP
- **Static Analysis**: PHPStan
- **Code Style**: Laravel Pint
- **Database**: SQLite (default), supports MySQL/PostgreSQL

## Requirements

- PHP 8.4 or higher
- Node.js 18.x or higher
- Composer 2.x
- SQLite (or MySQL/PostgreSQL if preferred)
- External Services:
   - A RapidApi account to access barcode lookups. See https://freewebapi.com/data-apis/barcode-lookup-api/
   - An OpenAI account (or other API compatible LLM service), used for parsing ingredients in a standardized way

## Local Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/nutriplan.git
   cd nutriplan
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Create your environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Create the SQLite database:
   ```bash
   touch database/database.sqlite
   ```

7. Run database migrations:
   ```bash
   php artisan migrate
   ```

8. Start the development server:
   ```bash
   composer dev
   ```
   **What it runs**:
    - Laravel development server
    - Queue listener
    - Log viewer (Pail)
    - Vite development server<br>
   **Note**: Uses concurrently to run all services in parallel with color-coded output

The application will be available at `http://localhost`.

## Testing

### Run all test suites:
```bash
composer test
```
**What it runs**:
  - Type coverage tests
  - Unit tests
  - Linting
  - Refactoring checks

### Run static analysis:
```bash
composer test:types
```
**What it runs**: 
   - PHPStan analysis

### Fix code style:
```bash
composer lint
```
**What it runs**:
  - Laravel Pint
  - NPM formatting
  - NPM linting

see [Composer Scripts](.cursor/rules/composer_scripts.md) for a complete list of available scripts.

## Database Structure

### Key Models

- **Recipe**: Core model for storing recipes
  - Belongs to a User
  - Has many RecipeIngredients through pivot
  - Belongs to many Categories 
  - Belongs to many Collections
  - Has one NutritionInformation
  - Can be favorited by many Users
  - Includes fields for title, slug, description, instructions, cooking/prep time, servings, source URL, and images

- **User**: Represents application users
  - Has many Recipes
  - Has many Collections
  - Has many ShoppingLists
  - Has many MealPlans
  - Can favorite many Recipes

- **Ingredient**: For recipe components
  - Belongs to many Recipes through RecipeIngredient pivot
  - Contains standardized ingredient information

- **RecipeIngredient**: Pivot model connecting Recipes and Ingredients
  - Contains amount, unit, and description for ingredients in specific recipes

- **Category**: For organizing recipes
  - Has many Recipes
  - Includes slug for friendly URLs

- **Collection**: User-created recipe collections
  - Belongs to a User
  - Has many Recipes
  - Includes slug for friendly URLs

- **MealPlan**: For planning meals over a time period
  - Belongs to a User
  - Has many MealPlanDays
  - Has many Recipes through MealPlanRecipe pivot
  - Contains start date, duration, and people count

- **MealPlanDay**: Represents a single day in a meal plan
  - Belongs to a MealPlan
  - Has many MealAssignments
  - Contains day number and calculated date

- **MealPlanRecipe**: Pivot model connecting MealPlans and Recipes
  - Contains scale factor and servings available

- **MealAssignment**: For assigning recipe servings to specific days
  - Belongs to a MealPlanDay
  - Related to MealPlanRecipe
  - Tracks cooking status and servings

- **ShoppingList**: For managing shopping items
  - Belongs to a User
  - Has many ShoppingListItems
  - Contains date range information

- **ShoppingListItem**: Individual items on a shopping list
  - Belongs to a ShoppingList
  - Tracks purchase status and quantity

- **NutritionInformation**: Stores nutritional data for recipes
  - Belongs to a Recipe
  - Contains various nutritional values

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Vue.js](https://vuejs.org) - The Progressive JavaScript Framework
- [Inertia.js](https://inertiajs.com) - The Modern Monolith
- [Tailwind CSS](https://tailwindcss.com) - A utility-first CSS framework
- [Spatie](https://spatie.be) - For their excellent Laravel packages
