# Documentation: Category.php

Original file: `app/Models/Category.php`

# Category Documentation

## Table of Contents
- [Introduction](#introduction)
- [Attributes](#attributes)
- [Methods](#methods)
  - [getSlugOptions](#getslugoptions)
  - [recipes](#recipes)
  
## Introduction

The `Category.php` file defines the `Category` model in the NutriPlan application, which is part of the AI-driven recipe recommendation system. This model represents the categories under which recipes can be organized, allowing users to browse and filter recipes based on specific topics or types. The model incorporates features for slug generation and defines relationships with the `Recipe` model, enabling efficient data handling and retrieval within the application's context.

## Attributes

The `Category` model includes the following attributes:

| Attribute Name | Type     | Description                                     |
|----------------|----------|-------------------------------------------------|
| `name`         | string   | The name of the category.                       |
| `description`  | string   | A brief description of the category.           |
| `is_active`    | boolean  | Indicates if the category is active (shown to users). |
| `slug`         | string   | A URL-friendly version of the category name, generated automatically. |

## Methods

### getSlugOptions

```php
public function getSlugOptions(): SlugOptions
```

#### Purpose
The `getSlugOptions` method defines how slugs are generated for the `Category` model.

#### Parameters
This method does not take any parameters.

#### Returns
- `SlugOptions`: A configuration object that specifies how to generate and store slugs for the model.

#### Functionality
This method uses the `Sluggable` package from Spatie to create a slug from the `name` attribute of the `Category`. The generated slug is then saved to the `slug` attribute of the model. 

The process is defined as follows:
1. Slugs are generated from the `name` field.
2. The generated slugs are stored in the `slug` attribute.

### recipes

```php
public function recipes(): BelongsToMany
```

#### Purpose
The `recipes` method establishes a many-to-many relationship between the `Category` and `Recipe` models.

#### Parameters
This method does not take any parameters.

#### Returns
- `BelongsToMany`: An instance of the `BelongsToMany` relationship, which represents the association between categories and recipes.

#### Functionality
The `recipes` method uses the `belongsToMany` Eloquent relationship to link categories with recipes. This setup allows each category to be associated with multiple recipes and vice versa. Eloquent will handle the underlying database relationships using a pivot table to facilitate this connection.

By defining this relationship, the `Category` model gains powerful methods for retrieving related `Recipe` instances and managing the connections dynamically through Eloquent's ORM capabilities.

## Conclusion
The `Category` model in this PHP codebase serves as an essential component within the NutriPlan application's architecture, enabling effective organization of recipes through category management. Its attributes and relationships simplify data interactions, ensuring that the best practices in coding and database relationships are followed. This documentation should assist developers in understanding both the structural and functional aspects of the `Category` model, facilitating enhanced usage or modification as needed in future development.