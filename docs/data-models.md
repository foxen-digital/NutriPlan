# Data Models - NutriPlan

This document describes all database models and their relationships in the NutriPlan application.

## Database

- **Default:** SQLite
- **Supported:** MySQL, PostgreSQL
- **ORM:** Eloquent (Laravel)

---

## Core Models

### User

Represents application users (extends Laravel's default user model).

**Relationships:**
- `hasMany` Recipes - User's created recipes
- `hasMany` Collections - User's recipe collections
- `hasMany` ShoppingLists - User's shopping lists
- `hasMany` MealPlans - User's meal plans
- `belongsToMany` Recipes (favorites) - Recipes favorited by user

**Attributes:**
- `id` - Primary key
- `name` - User name
- `email` - Email address (unique)
- `password` - Hashed password
- `slug` - URL-friendly identifier

---

### Recipe

Core model for storing recipes.

**Relationships:**
- `belongsTo` User - Recipe creator
- `belongsToMany` Category - Recipe categories
- `belongsToMany` Ingredient - Recipe ingredients (via RecipeIngredient pivot)
- `belongsToMany` Collection - Collections containing this recipe
- `hasOne` NutritionInformation - Nutritional data
- `belongsToMany` User (favoritedBy) - Users who favorited this recipe

**Attributes:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `title` - Recipe title
- `slug` - URL-friendly identifier (route key)
- `description` - Recipe description
- `instructions` - Cooking instructions
- `prep_time` - Preparation time (minutes, integer)
- `cooking_time` - Cooking time (minutes, integer)
- `servings` - Number of servings (integer)
- `url` - Source URL (for imported recipes)
- `images` - Image URLs (array, cast)
- `is_public` - Public visibility flag (boolean, cast)

**Methods:**
- `isImported()` - Returns true if recipe was imported from URL
- `getMeasurementForIngredient(Ingredient)` - Get measurement for specific ingredient

---

### Category

For organizing recipes into categories.

**Relationships:**
- `hasMany` Recipe - Recipes in this category

**Attributes:**
- `id` - Primary key
- `name` - Category name
- `slug` - URL-friendly identifier

---

### Collection

User-created recipe collections for organization.

**Relationships:**
- `belongsTo` User - Collection owner
- `belongsToMany` Recipe - Recipes in this collection

**Attributes:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `name` - Collection name
- `slug` - URL-friendly identifier

---

### Ingredient

Standardized ingredient information.

**Relationships:**
- `belongsToMany` Recipe - Recipes using this ingredient (via RecipeIngredient pivot)

**Attributes:**
- `id` - Primary key
- `name` - Ingredient name

**Note:** Description field was removed in migration `2025_04_12_225123`

---

### RecipeIngredient (Pivot Model)

Connects Recipes and Ingredients with additional data.

**Relationships:**
- `belongsTo` Recipe
- `belongsTo` Ingredient

**Attributes:**
- `recipe_id` - Foreign key to recipes
- `ingredient_id` - Foreign key to ingredients
- `amount` - Quantity amount
- `unit` - Measurement unit (enum or string)
- `description` - Additional description (e.g., "chopped", "diced")

---

### NutritionInformation

Stores nutritional data for recipes.

**Relationships:**
- `belongsTo` Recipe - Parent recipe

**Attributes:**
- `id` - Primary key
- `recipe_id` - Foreign key to recipes
- `calories` - Calorie count
- `protein` - Protein content (grams)
- `carbohydrates` - Carbohydrate content (grams)
- `fat` - Fat content (grams)
- `fiber` - Fiber content (grams)
- `sugar` - Sugar content (grams)
- `sodium` - Sodium content (milligrams)

---

### MealPlan

For planning meals over a time period.

**Relationships:**
- `belongsTo` User - Meal plan owner
- `belongsToMany` Recipe (via MealPlanRecipe pivot) - Recipes in the plan
- `hasMany` MealPlanDay - Days in the plan

**Attributes:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `name` - Plan name
- `start_date` - Plan start date (date, cast)
- `duration` - Number of days (integer, cast)
- `people_count` - Number of people (integer, cast)

**Methods:**
- `getEndDateAttribute()` - Calculate end date based on start_date + duration

---

### MealPlanRecipe (Pivot Model)

Connects MealPlans and Recipes with scaling information.

**Relationships:**
- `belongsTo` MealPlan
- `belongsTo` Recipe

**Attributes:**
- `id` - Primary key
- `meal_plan_id` - Foreign key to meal_plans
- `recipe_id` - Foreign key to recipes
- `scale_factor` - Recipe scaling multiplier
- `servings_available` - Available servings from this recipe

---

### MealPlanDay

Represents a single day in a meal plan.

**Relationships:**
- `belongsTo` MealPlan - Parent meal plan
- `hasMany` MealAssignment - Meal assignments for this day

**Attributes:**
- `id` - Primary key
- `meal_plan_id` - Foreign key to meal_plans
- `day_number` - Day number in plan (integer)
- `date` - Calculated date (date)

---

### MealAssignment

For assigning recipe servings to specific days and meals.

**Relationships:**
- `belongsTo` MealPlanDay - Parent day
- `belongsTo` MealPlanRecipe - Associated recipe

**Attributes:**
- `id` - Primary key
- `meal_plan_day_id` - Foreign key to meal_plan_days
- `meal_plan_recipe_id` - Foreign key to meal_plan_recipes
- `meal_type` - Type of meal (breakfast, lunch, dinner, snack)
- `servings` - Number of servings (integer)
- `to_cook` - Cooking reminder flag (boolean)
- `order` - Display order (integer)

---

### ShoppingList

For managing shopping items.

**Relationships:**
- `belongsTo` User - Shopping list owner
- `hasMany` ShoppingListItem - Items in the list

**Attributes:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `name` - List name
- `date_from` - Start date for shopping (date)
- `date_to` - End date for shopping (date)

---

### ShoppingListItem

Individual items on a shopping list.

**Relationships:**
- `belongsTo` ShoppingList - Parent shopping list

**Attributes:**
- `id` - Primary key
- `shopping_list_id` - Foreign key to shopping_lists
- `name` - Item name/description
- `quantity` - Quantity needed
- `purchased` - Purchase status (boolean)
- `order` - Display order (integer)

---

## Database Schema

### Migration Order

1. System tables (cache, jobs, users)
2. Core recipe tables (recipes, categories, ingredients)
3. Recipe relationships (category_recipe, ingredient_recipe)
4. Recipe enhancements (source fields, slug, is_public)
5. Collections and favorites
6. Meal planning tables (meal_plans, meal_plan_recipe, meal_plan_days, meal_assignments)
7. Shopping list tables (shopping_lists, shopping_list_items)
8. Authentication (personal_access_tokens)

### Key Indexes

- `slug` indexes on users, recipes, categories, collections (for routing)
- Foreign key indexes on all relationships
- Composite indexes for pivot tables

---

## Data Integrity

### Soft Deletes
Not currently implemented on any models.

### Timestamps
All models use Eloquent's default `created_at` and `updated_at` timestamps.

### Validation
Request validation classes handle data validation:
- `CreateRecipeRequest`, `UpdateRecipeRequest`
- `StoreCollectionRequest`, `UpdateCollectionRequest`
- `StoreMealPlanRecipeRequest`, `StoreShoppingListItemRequest`
- And more...

See [documentation/app/Http/Requests/](../documentation/app/Http/Requests/) for full list.
