# Documentation: RecipeParser.php

Original file: `app/Services/RecipeParser.php`

# RecipeParser Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Constructor](#constructor)
3. [Static Methods](#static-methods)
   - [fromItems](#fromitems)
4. [Instance Methods](#instance-methods)
   - [setRecipe](#setrecipe)
   - [parse](#parse)
   - [parse_name](#parse_name)
   - [parse_description](#parse_description)
   - [parse_recipeyield](#parse_recipeyield)
   - [parse_preptime](#parse_preptime)
   - [parse_cooktime](#parse_cooktime)
   - [parse_image](#parse_image)
   - [parse_recipeingredient](#parse_recipeingredient)
   - [parse_recipeinstructions](#parse_recipeinstructions)
   - [parse_author](#parse_author)
   - [parse_keywords](#parse_keywords)
   - [parse_recipecategory](#parse_recipecategory)
   - [parseCommaSeparatedString](#parsecommaseparatedstring)

## Introduction
The `RecipeParser` class is designed to handle the parsing of recipe data, typically extracted from structured items such as web pages or APIs. This class utilizes structured data formats (like Schema.org) to populate a `Recipe` model with relevant information including the title, ingredients, instructions, cooking times, and nutritional information. It manages the creation and updating of recipes and their associated categories and ingredients within the application, ensuring that the data is stored in a way that adheres to the application's business logic.

## Constructor
```php
public function __construct(
    private string $title = '',
    private string $description = '',
    private readonly string $url = '',
    private string $author = '',
    array $ingredients = [],
    array $steps = [],
    private string $yield = '',
    private int $prep_time = 0,
    private int $cooking_time = 0,
    private int $servings = 0,
    array $images = [],
    array $categories = [],
    private ?IngredientNormalizationService $normalizationService = null,
    private ?InstructionNormalizationService $instructionNormalizationService = null,
    private readonly NutritionParser $nutrition_parser = new NutritionParser()
)
```
### Purpose
The constructor initializes a `RecipeParser` instance with the required parameters for parsing recipe information. It also injects any normalization services needed for ingredients and instructions.

### Parameters
- `string $title` - The title of the recipe.
- `string $description` - A brief description of the recipe.
- `string $url` - The source URL of the recipe.
- `string $author` - The author of the recipe.
- `array<int, string> $ingredients` - An array of ingredient strings.
- `array<int, string> $steps` - An array of preparation steps.
- `string $yield` - The yield or number of servings this recipe provides.
- `int $prep_time` - The preparation time in minutes.
- `int $cooking_time` - The cooking time in minutes.
- `int $servings` - The number of servings.
- `array<int, string> $images` - An array of image URLs.
- `array<int, string> $categories` - An array of category names.
- `?IngredientNormalizationService $normalizationService` - The service for normalizing ingredients (optional).
- `?InstructionNormalizationService $instructionNormalizationService` - The service for normalizing cooking instructions (optional).
- `NutritionParser $nutrition_parser` - The parser dedicated to extracting nutrition information.

### Functionality
- The constructor checks and initializes the ingredient and instruction normalization services.
- It also initializes a `NutritionParser` service for parsing nutrition data.

## Static Methods

### fromItems
```php
public static function fromItems(array|Item $items, string $url): ?Recipe
```
#### Purpose
This static method allows for creating a `RecipeParser` instance from structured data items.

#### Parameters
- `array|Item $items` - An array of structured data items or a single item that may represent a recipe.
- `string $url` - The URL associated with the recipe data.

#### Return Value
- Returns an instance of `Recipe` if parsing is successful; otherwise, returns `null`.

#### Functionality
- Iterates over the provided items to find the one that contains 'recipe' in its types.
- Parses this item to create a new `RecipeParser` instance, extracts details, and returns a `Recipe` object.

## Instance Methods

### setRecipe
```php
public function setRecipe(Recipe $recipe): self
```
#### Purpose
Sets an existing `Recipe` object to be updated by the parser.

#### Parameters
- `Recipe $recipe` - The `Recipe` model that will be updated with parsed values.

#### Return Value
- Returns the current instance of `RecipeParser`.

#### Functionality
- Assigns the provided `Recipe` to the internal `recipe` property for further updates during parsing.

### parse
```php
public function parse(Item $item): Recipe
```
#### Purpose
Parses a `Item` object to extract recipe data and updates or creates a `Recipe` record.

#### Parameters
- `Item $item` - The item containing structured recipe data.

#### Return Value
- Returns a `Recipe` model populated with parsed data.

#### Functionality
- Iterates through properties of the `Item`, dynamically calling methods to parse each property.
- Handles nutrition data specifically and stores it in the `nutrition` property.
- If an existing recipe is not set, it attempts to find or create a new `Recipe`.
- Syncs categories, normalizes ingredients, and attaches them to the recipe.
- If nutrition information is available, it updates or creates the nutrition data for the recipe.

### parse_name
```php
private function parse_name(array|string $values): void
```
#### Purpose
Parses the name of the recipe from the `Item`.

#### Parameters
- `array|string $values` - The name values, which can be an array or a single string.

#### Functionality
- Sets the `title` property to the first value found.

### parse_description
```php
private function parse_description(array|string $values): void
```
#### Purpose
Parses the description of the recipe.

#### Parameters
- `array|string $values` - The description values.

#### Functionality
- Assigns the `description` property to the first value from the input.

### parse_recipeyield
```php
public function parse_recipeyield(array|string $values): void
```
#### Purpose
Parses the yield information for the recipe, extracting the number of servings.

#### Parameters
- `array|string $values` - The yield values.

#### Functionality
- Uses regex to extract the number of servings from the yield string, updating the `servings` and `yield` properties accordingly.

### parse_preptime
```php
public function parse_preptime(array|string $values): void
```
#### Purpose
Parses the preparation time for the recipe.

#### Parameters
- `array|string $values` - The preparation time values.

#### Functionality
- Uses regex to parse ISO