# Documentation: Ingredient.php

Original file: `app/Models/Ingredient.php`

# Ingredient Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Definition](#class-definition)
- [Properties](#properties)
- [Methods](#methods)
  - [getSlugOptions](#getslugoptions)
  - [recipes](#recipes)

## Introduction
The `Ingredient` class defines a model for an ingredient within the NutriPlan application. This class utilizes the Laravel framework for interacting with the underlying database and includes features for managing slugs that are generated from the ingredient's name. The `Ingredient` model facilitates relationships with recipes, allowing the application to link ingredients to specific recipes while also storing additional details like the amount and unit of measurement associated with each ingredient.

## Class Definition
```php
class Ingredient extends Model
```
The `Ingredient` class extends Laravel's `Model` class, leveraging the powerful features of Eloquent ORM to manage the interaction with the ingredients data stored in the database.

## Properties
- **guarded**: An array that specifies which attributes should not be mass assignable. In this case, it's an empty array, meaning all attributes can be mass assigned.
- **casts**: An array that specifies how certain attributes should be cast when they are accessed or manipulated. Here, `is_common` is cast to a boolean.

## Methods

### getSlugOptions
```php
public function getSlugOptions(): SlugOptions
```
#### Purpose
The `getSlugOptions` method defines how slugs are generated for the ingredient.

#### Parameters
- None

#### Return Values
- Returns an instance of `SlugOptions`.

#### Functionality
This method uses the `Spatie\Sluggable` package to determine the source field for slug generation and the field where the slug should be saved. Specifically, slugs are generated from the `name` field of the ingredient and stored in the `slug` field.

### recipes
```php
public function recipes(): BelongsToMany
```
#### Purpose
The `recipes` method establishes a many-to-many relationship between the `Ingredient` model and the `Recipe` model.

#### Parameters
- None

#### Return Values
- Returns a `BelongsToMany` relationship instance.

#### Functionality
This method allows the application to retrieve all recipes that are associated with a particular ingredient. It also specifies additional pivot table attributes (`amount`, `unit`, `description`) that can be accessed when querying this relationship. This setup enables seamless management of links between recipes and the ingredients that constitute them.

## Summary
The `Ingredient` class is an essential part of the NutriPlan application's data management. By defining the `Ingredient` model, the application can effectively handle ingredient data, generate slugs for SEO-friendly URLs, and maintain relationships with recipes. Understanding each method and property allows developers to extend and interact with the data layer effectively, providing a better user experience within the application.