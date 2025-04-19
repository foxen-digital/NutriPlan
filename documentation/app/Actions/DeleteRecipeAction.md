# Documentation: DeleteRecipeAction.php

Original file: `app/Actions/DeleteRecipeAction.php`

# DeleteRecipeAction Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method Documentation](#method-documentation)
  - [execute](#execute)

## Introduction
The `DeleteRecipeAction.php` file defines the `DeleteRecipeAction` class, which is responsible for handling the deletion of recipe records within the NutriPlan PHP application. This class performs critical actions related to recipe management, specifically ensuring the proper removal of recipes and their associated data from the database. This functionality is essential for maintaining data integrity and ensuring that users can manage their recipes effectively.

## Method Documentation

### execute
```php
public function execute(Recipe $recipe): bool
```
#### Purpose
The `execute` method deletes a given recipe from the database along with its related records, ensuring that all associated data is handled appropriately during the deletion process.

#### Parameters
- **Recipe $recipe**: An instance of the `Recipe` model that represents the recipe to be deleted. This parameter is type-hinted to ensure that only valid `Recipe` objects are passed to the method.

#### Return Values
- **bool**: The method returns `true` if the recipe is successfully deleted. If the recipe does not exist, a `ModelNotFoundException` is thrown.

#### Functionality
1. **Existence Check**: 
   - The method first checks if the provided `recipe` instance exists in the database. If the recipe does not exist (`$recipe->exists` evaluates to false), a `ModelNotFoundException` is thrown with the message 'Recipe not found'. This prevents unintentional deletions and ensures that actions are performed on existing records only.

2. **Delete Related Nutrition Information**: 
   - If the `recipe` has associated nutrition information (`$recipe->nutritionInformation`), this associated record is deleted using the `delete()` method. This step is crucial for maintaining the integrity of related data and ensuring that stale or orphaned records do not remain in the database.

3. **Database Cascade Deletions**: 
   - The method comments note that any remaining relationships linked to the `Recipe` will automatically be deleted by database cascades or through the handling of pivot tables. This mechanism simplifies the deletion process by relying on existing database constraints and relationships.

4. **Final Deletion**: 
   - Finally, the method attempts to delete the `recipe` record itself using the `delete()` method on the `Recipe` model instance. The success of this operation will determine the boolean return value of the method.

The `DeleteRecipeAction` class encapsulates the logic necessary for safely and efficiently removing recipe data, thus contributing to the overall functionality and data management practices of the NutriPlan application.