# API Contracts - NutriPlan

This document describes all API endpoints available in the NutriPlan application.

## Authentication

All API routes require authentication. The application uses Laravel Sanctum for token-based authentication and session authentication.

### Authentication Methods

1. **Session Authentication** (Web routes)
   - Used for Inertia.js requests
   - Middleware: `auth:sanctum`
   - Requires user to be logged in via web session

2. **Token Authentication** (API routes)
   - Used for external API requests
   - Middleware: `auth:sanctum`
   - Requires Bearer token in Authorization header

---

## RESTful API Endpoints

### User Information

#### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

**Response:** User object

**Authentication:** Required

---

### Recipe Search

#### Search Recipes
```http
GET /api/recipes/search?q={query}
```

**Parameters:**
- `q` (string) - Search query

**Response:** JSON array of matching recipes

**Authentication:** Required (session + sanctum)

---

### Barcode Lookup

#### Lookup Product by Barcode
```http
POST /api/barcode-lookup
```

**Body:**
```json
{
  "barcode": "1234567890123"
}
```

**Response:** Product information from barcode API

**Authentication:** Required (session + sanctum)

---

### Item Search

#### Search Shopping Items
```http
GET /api/item-search?q={query}
```

**Parameters:**
- `q` (string) - Search query for shopping items

**Response:** JSON array of matching items

**Authentication:** Required (session + sanctum)

---

### Recipe Import (External)

#### Import Recipe via Extension
```http
POST /api/recipes/import-via-extension
Authorization: Bearer {token}
```

**Body:**
```json
{
  "url": "https://example.com/recipe"
}
```

**Response:** Imported recipe data

**Authentication:** Required (token only - for browser extensions)

---

## Web Routes (Inertia.js)

These routes return Vue components via Inertia.js. They follow RESTful conventions.

### Recipe Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/recipes` | recipes.index | List all recipes |
| GET | `/recipes/create` | recipes.create | Show create recipe form |
| POST | `/recipes` | recipes.store | Store new recipe |
| GET | `/recipes/{recipe}` | recipes.show | Show single recipe |
| GET | `/recipes/{recipe}/edit` | recipes.edit | Show edit form |
| PUT/PATCH | `/recipes/{recipe}` | recipes.update | Update recipe |
| DELETE | `/recipes/{recipe}` | recipes.destroy | Delete recipe |
| POST | `/recipes/import` | recipes.import | Import recipe from URL |
| GET | `/recipes/by/{user}` | recipes.by-user | List recipes by user |

### Category Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/categories` | categories.index | List all categories |
| GET | `/categories/{category}` | categories.show | Show single category |
| POST | `/categories` | - | Create new category |

### Collection Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/collections` | collections.index | List all collections |
| GET | `/collections/create` | collections.create | Show create form |
| POST | `/collections` | collections.store | Store new collection |
| GET | `/collections/{collection}` | collections.show | Show single collection |
| GET | `/collections/{collection}/edit` | collections.edit | Show edit form |
| PUT/PATCH | `/collections/{collection}` | collections.update | Update collection |
| DELETE | `/collections/{collection}` | collections.destroy | Delete collection |
| POST | `/collections/add-recipe` | collections.add-recipe | Add recipe to collection |
| DELETE | `/collections/{collection}/recipes/{recipe}` | collections.remove-recipe | Remove recipe from collection |

### Favorite Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/favorites` | favorites.index | List favorite recipes |
| POST | `/recipes/{recipe}/favorite` | recipes.favorite | Toggle favorite status |

### Meal Plan Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/meal-plans` | meal-plans.index | List all meal plans |
| GET | `/meal-plans/create` | meal-plans.create | Show create form |
| POST | `/meal-plans` | meal-plans.store | Store new meal plan |
| GET | `/meal-plans/{mealPlan}` | meal-plans.show | Show single meal plan |
| DELETE | `/meal-plans/{mealPlan}` | meal-plans.destroy | Delete meal plan |
| POST | `/meal-plans/add-recipe` | meal-plans.add-recipe | Add recipe to meal plan |
| DELETE | `/meal-plans/{mealPlan}/recipes/{recipe}` | meal-plans.remove-recipe | Remove recipe from meal plan |
| POST | `/meal-plans/{mealPlan}/copy` | meal-plans.copy | Copy meal plan |
| POST | `/meal-plans/{mealPlan}/shopping-list` | meal-plans.generate-shopping-list | Generate shopping list from plan |

### Shopping List Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/shopping-lists` | shopping-lists.index | List all shopping lists |
| GET | `/shopping-lists/create` | shopping-lists.create | Show create form |
| POST | `/shopping-lists` | shopping-lists.store | Store new shopping list |
| GET | `/shopping-lists/{shoppingList}` | shopping-lists.show | Show single shopping list |
| GET | `/shopping-lists/{shoppingList}/edit` | shopping-lists.edit | Show edit form |
| PUT/PATCH | `/shopping-lists/{shoppingList}` | shopping-lists.update | Update shopping list |
| DELETE | `/shopping-lists/{shoppingList}` | shopping-lists.destroy | Delete shopping list |
| POST | `/shopping-lists/{shoppingList}/items` | shopping-lists.items.store | Add item to shopping list |
| PUT | `/shopping-lists/{shoppingList}/items/{item}` | shopping-lists.items.update | Update shopping list item |
| DELETE | `/shopping-lists/{shoppingList}/items/{item}` | shopping-lists.items.destroy | Delete shopping list item |
| POST | `/shopping-lists/{shoppingList}/items/{item}/toggle-purchased` | shopping-lists.items.toggle-purchased | Toggle purchased status |
| PUT | `/shopping-lists/{shoppingList}/order-items` | shopping-lists.items.order | Reorder items |

### Meal Assignment Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| POST | `/meal-assignments` | meal-assignments.store | Create meal assignment |
| PUT | `/meal-assignments/{mealAssignment}` | meal-assignments.update | Update meal assignment |
| DELETE | `/meal-assignments/{mealAssignment}` | meal-assignments.destroy | Delete meal assignment |
| POST | `/meal-assignments/{mealAssignment}/toggle-cook` | meal-assignments.toggle-cook | Toggle cooking flag |
| PATCH | `/meal-assignments/{mealAssignment}/move` | meal-assignments.move | Move assignment |
| PATCH | `/meal-plan-days/{meal_plan_day}/assignments/order` | meal-plan-days.assignments.reorder | Reorder assignments |

### Settings Routes

Settings routes for user profile, password, and API tokens are defined in `routes/settings.php`.

### Authentication Routes

Authentication routes (login, register, password reset) are defined in `routes/auth.php`.

---

## External Services Integration

### Barcode Lookup API
- **Provider:** FreeWebAPI (Barcode Lookup API)
- **Purpose:** Product information lookup via barcode
- **Used in:** Shopping list item creation

### Recipe Import
- **Provider:** OpenAI API (or compatible LLM)
- **Purpose:** AI-powered ingredient parsing and normalization
- **Used in:** Recipe import from URLs

---

## Response Formats

### Success Response
```json
{
  "success": true,
  "data": { ... }
}
```

### Error Response
```json
{
  "message": "Error description",
  "errors": { ... }
}
```

### Validation Error Response
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Error message"]
  }
}
```
