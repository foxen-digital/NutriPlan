# Documentation: RecipeSeeder.php

Original file: `database/seeders/RecipeSeeder.php`

# RecipeSeeder Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method: `run`](#method-run)

---

## Introduction

The `RecipeSeeder` class is part of the database seeders within the NutriPlan application, specifically tailored for seeding initial data into the database. It is responsible for populating the `Recipe`, `Category`, `Ingredient`, and `User` tables with sample data necessary for testing and initial application setup. This helps ensure that there is sufficient data available for development and testing, which can facilitate the overall development process by providing a working dataset.

---

## Method: `run`

### Purpose

The `run` method runs the entire seeding process for the application. It creates predefined categories, ingredients, a test user, and generates multiple recipe entries with the associated categories and ingredients.

### Parameters

This method does not take any parameters.

### Return Values

This method does not return any values; it interacts directly with the database to create records.

### Functionality

The `run` method performs the following actions:

1. **Create Common Categories**  
   It initializes a list of common meal categories such as 'Breakfast', 'Lunch', 'Dinner', etc., and uses a factory to create them in the database. Each category is given a randomly generated description.

   ```php
   $categories = [
       'Breakfast',
       'Lunch',
       'Dinner',
       'Dessert',
       'Snacks',
       'Vegetarian',
       'Vegan',
       'Gluten Free',
       'Quick & Easy',
       'Healthy',
   ];

   $categoryModels = collect($categories)->map(fn ($name) => Category::factory()->create([
       'name' => $name,
       'description' => fake()->sentence(),
   ]));
   ```

2. **Create Common Ingredients**  
   Similar to categories, the method initializes a list of common cooking ingredients like 'Salt', 'Garlic', 'Chicken', etc. Ingredients are also created in the database using a factory, along with randomly generated descriptions.

   ```php
   $ingredients = [
       'Salt',
       'Black Pepper',
       'Olive Oil',
       'Garlic',
       'Onion',
       'Butter',
       //...
   ];

   $ingredientModels = collect($ingredients)->map(fn ($name) => Ingredient::factory()->common()->create([
       'name' => $name,
       'description' => fake()->sentence(),
   ]));
   ```

3. **Create Additional Random Ingredients**  
   The method generates an additional 30 random ingredients using the `Ingredient` factory. This enriches the variety of ingredients available for the recipes.

   ```php
   $additionalIngredients = Ingredient::factory()->count(30)->create();
   ```

4. **Create a Test User**  
   A test user named 'Test User' is created with an email address 'test@example.com' to ensure that there is a reference user associated with the recipes created.

   ```php
   $user = User::factory()->create([
       'name' => 'Test User',
       'email' => 'test@example.com',
   ]);
   ```

5. **Create Recipes**  
   The method creates 50 unique recipes. For each recipe, it:
   - Randomly assigns 1 to 3 categories from the previously created categories.
   - Randomly assigns between 3 to 10 ingredients with specific amounts and measurement units for each recipe.

   ```php
   Recipe::factory()
       ->count(50)
       ->create(['user_id' => $user->id])
       ->each(function (Recipe $recipe) use ($categoryModels, $allIngredients): void {
           // Attach 1-3 random categories
           $recipe->categories()->attach(
               $categoryModels->random(fake()->numberBetween(1, 3))->pluck('id')
           );

           // Attach 3-10 random ingredients with amounts
           $recipe->ingredients()->attach(
               $allIngredients->random(fake()->numberBetween(3, 10))->mapWithKeys(fn ($ingredient) => [
                   $ingredient->id => [
                       'amount' => fake()->randomFloat(2, 0.25, 10),
                       'unit' => fake()->randomElement(array_column(MeasurementUnit::cases(), 'value')),
                   ],
               ])->toArray()
           );
       });
   ```

This comprehensive method ensures that the necessary seed data is available for the application's development and testing phases, creating a robust data-driven environment.

---

By following this documentation, developers should be able to understand how to use the `RecipeSeeder` class effectively while also gaining insight into the structure and relationships of data within the NutriPlan application.