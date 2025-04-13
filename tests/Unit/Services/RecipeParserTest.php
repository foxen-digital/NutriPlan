<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\NutritionInformation;
use App\Models\Recipe;
use App\Services\IngredientNormalizationService;
use App\Services\InstructionNormalizationService;
use App\Services\NutritionParser;
use App\Services\RecipeParser;
use Brick\StructuredData\Item;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $nutritionParser = new NutritionParser();

    // Mock the IngredientNormalizationService
    $normalizationService = mock(IngredientNormalizationService::class);
    $normalizationService->shouldReceive('normalize')->andReturn([]);

    // Mock the InstructionNormalizationService
    $instructionNormalizationService = mock(InstructionNormalizationService::class);
    $instructionNormalizationService->shouldReceive('normalize')->andReturn('Normalized Instructions');

    $this->parser = new RecipeParser(
        nutrition_parser: $nutritionParser,
        normalizationService: $normalizationService,
        instructionNormalizationService: $instructionNormalizationService
    );
});

it('parses a single category from keywords', function () {
    $user = createUser();
    Auth::login($user);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'keywords' => ['Dinner'],
        ]);

    $recipe = $this->parser->parse($item);

    expect($recipe->categories)
        ->toHaveCount(1)
        ->first()->name->toBe('Dinner');
});

it('parses multiple categories from comma-separated keywords', function () {
    $user = createUser();
    Auth::login($user);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'keywords' => ['Dinner, Healthy, Quick Meals'],
        ]);

    $recipe = $this->parser->parse($item);

    expect($recipe->categories)
        ->toHaveCount(3)
        ->sequence(
            fn ($category) => $category->name->toBe('Dinner'),
            fn ($category) => $category->name->toBe('Healthy'),
            fn ($category) => $category->name->toBe('Quick Meals'),
        );
});

it('parses categories from recipeCategory', function () {
    $user = createUser();
    Auth::login($user);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'recipeCategory' => ['Main Course, Italian'],
        ]);

    $recipe = $this->parser->parse($item);

    expect($recipe->categories)
        ->toHaveCount(2)
        ->sequence(
            fn ($category) => $category->name->toBe('Main Course'),
            fn ($category) => $category->name->toBe('Italian'),
        );
});

it('combines categories from keywords and recipeCategory', function () {
    $user = createUser();
    Auth::login($user);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'keywords' => ['Dinner, Healthy'],
            'recipeCategory' => ['Main Course, Italian'],
        ]);

    $recipe = $this->parser->parse($item);

    expect($recipe->categories)
        ->toHaveCount(4)
        ->sequence(
            fn ($category) => $category->name->toBe('Dinner'),
            fn ($category) => $category->name->toBe('Healthy'),
            fn ($category) => $category->name->toBe('Main Course'),
            fn ($category) => $category->name->toBe('Italian'),
        );
});

it('reuses existing categories', function () {
    $user = createUser();
    Auth::login($user);

    $existingCategory = Category::factory()->create(['name' => 'Dinner']);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'keywords' => ['Dinner, Healthy'],
        ]);

    $recipe = $this->parser->parse($item);

    expect($recipe->categories)
        ->toHaveCount(2)
        ->and($recipe->categories->first()->id)
        ->toBe($existingCategory->id);
});

it('trims whitespace from category names', function () {
    $user = createUser();
    Auth::login($user);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'keywords' => ['  Dinner  ,  Healthy  '],
        ]);

    $recipe = $this->parser->parse($item);

    expect($recipe->categories)
        ->toHaveCount(2)
        ->sequence(
            fn ($category) => $category->name->toBe('Dinner'),
            fn ($category) => $category->name->toBe('Healthy'),
        );
});

it('filters out empty category names', function () {
    $user = createUser();
    Auth::login($user);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'keywords' => ['Dinner,,  , Healthy'],
        ]);

    $recipe = $this->parser->parse($item);

    expect($recipe->categories)
        ->toHaveCount(2)
        ->sequence(
            fn ($category) => $category->name->toBe('Dinner'),
            fn ($category) => $category->name->toBe('Healthy'),
        );
});

it('parses nutrition information', function () {
    $user = createUser();
    Auth::login($user);

    // Create a mock NutritionParser that returns expected nutrition data
    $nutritionParser = mock(NutritionParser::class);
    $nutritionData = [
        'calories' => '240 cal',
        'carbohydrate_content' => '37g',
        'protein_content' => '4g',
        'fat_content' => '9g',
        'fiber_content' => '2g',
        'sugar_content' => '5g',
        'cholesterol_content' => '0mg',
        'sodium_content' => '200mg',
        'saturated_fat_content' => '2g',
        'trans_fat_content' => '0g',
        'unsaturated_fat_content' => '7g',
        'serving_size' => '1 serving',
    ];
    $nutritionParser->shouldReceive('parse')->andReturn($nutritionData);

    $parser = new RecipeParser(nutrition_parser: $nutritionParser);

    $item = mock(Item::class);
    $item->shouldReceive('getTypes')->andReturn(['Recipe']);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'name' => ['Test Recipe'],
            'nutrition' => [
                [
                    'calories' => '240 calories',
                    'carbohydrateContent' => '37g',
                    'proteinContent' => '4g',
                    'fatContent' => '9g',
                    'fiberContent' => '2g',
                    'sugarContent' => '5g',
                    'cholesterolContent' => '0mg',
                    'sodiumContent' => '200mg',
                    'saturatedFatContent' => '2g',
                    'transFatContent' => '0g',
                    'unsaturatedFatContent' => '7g',
                    'servingSize' => '1 serving',
                ]
            ],
        ]);

    $recipe = $parser->parse($item);

    expect($recipe->nutritionInformation)
        ->toBeInstanceOf(NutritionInformation::class)
        ->and($recipe->nutritionInformation->calories)->toBe('240 cal')
        ->and($recipe->nutritionInformation->carbohydrate_content)->toBe('37g')
        ->and($recipe->nutritionInformation->protein_content)->toBe('4g')
        ->and($recipe->nutritionInformation->fat_content)->toBe('9g')
        ->and($recipe->nutritionInformation->fiber_content)->toBe('2g')
        ->and($recipe->nutritionInformation->sugar_content)->toBe('5g')
        ->and($recipe->nutritionInformation->cholesterol_content)->toBe('0mg')
        ->and($recipe->nutritionInformation->sodium_content)->toBe('200mg')
        ->and($recipe->nutritionInformation->saturated_fat_content)->toBe('2g')
        ->and($recipe->nutritionInformation->trans_fat_content)->toBe('0g')
        ->and($recipe->nutritionInformation->unsaturated_fat_content)->toBe('7g')
        ->and($recipe->nutritionInformation->serving_size)->toBe('1 serving');
});

it('updates existing nutrition information', function () {
    $user = createUser();
    Auth::login($user);

    $recipe = Recipe::factory()->create();
    $existingNutrition = NutritionInformation::factory()->create([
        'recipe_id' => $recipe->id,
        'calories' => '200 cal',
        'protein_content' => '5g',
    ]);

    // Create a mock NutritionParser that returns expected nutrition data
    $nutritionParser = mock(NutritionParser::class);
    $nutritionData = [
        'calories' => '240 cal',
        'protein_content' => '10g',
    ];
    $nutritionParser->shouldReceive('parse')->andReturn($nutritionData);

    $parser = new RecipeParser(nutrition_parser: $nutritionParser);

    $item = mock(Item::class);
    $item->shouldReceive('getTypes')->andReturn(['Recipe']);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'name' => [$recipe->title],
            'url' => [$recipe->url],
            'nutrition' => [
                [
                    'calories' => '240 calories',
                    'proteinContent' => '10g',
                ],
            ],
        ]);

    $parser->setRecipe($recipe);
    $updatedRecipe = $parser->parse($item);

    expect($updatedRecipe->id)->toBe($recipe->id)
        ->and($updatedRecipe->nutritionInformation->id)->toBe($existingNutrition->id)
        ->and($updatedRecipe->nutritionInformation->calories)->toBe('240 cal')
        ->and($updatedRecipe->nutritionInformation->protein_content)->toBe('10g');
});

it('handles missing nutrition information', function () {
    $user = createUser();
    Auth::login($user);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'name' => 'Test Recipe',
        ]);

    $recipe = $this->parser->parse($item);

    expect($recipe->nutritionInformation)->toBeNull();
});

it('parses ingredients using the normalization service', function () {
    $user = createUser();
    Auth::login($user);

    // Normalized ingredient data that would be returned by the normalization service
    $normalizedIngredients = [
        [
            'base_name' => 'olive oil',
            'quantity' => 2,
            'unit' => 'tbsp',
            'preparation_notes' => 'extra virgin',
            'description' => 'extra virgin olive oil',
            'original_string' => '2 tbsp extra virgin olive oil'
        ],
        [
            'base_name' => 'garlic',
            'quantity' => 3,
            'unit' => 'clove',
            'preparation_notes' => 'minced',
            'description' => 'garlic, minced',
            'original_string' => '3 cloves garlic, minced'
        ]
    ];

    // Mock the normalization service
    $normalizationService = mock(IngredientNormalizationService::class);
    $normalizationService->shouldReceive('normalize')
        ->with(['2 tbsp extra virgin olive oil', '3 cloves garlic, minced'])
        ->andReturn($normalizedIngredients);

    $parser = new RecipeParser(normalizationService: $normalizationService);

    // Create ingredients in the database
    $oliveOil = Ingredient::factory()->create(['name' => 'olive oil']);
    $garlic = Ingredient::factory()->create(['name' => 'garlic']);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'name' => ['Test Recipe'],
            'recipeIngredient' => [
                '2 tbsp extra virgin olive oil',
                '3 cloves garlic, minced'
            ],
        ]);

    $recipe = $parser->parse($item);

    // Verify ingredients were attached with correct pivot data
    expect($recipe->ingredients)->toHaveCount(2);

    // Find olive oil ingredient and check its pivot data
    $recipeOliveOil = $recipe->ingredients->firstWhere('name', 'olive oil');
    expect($recipeOliveOil)->not->toBeNull()
        ->and($recipeOliveOil->pivot->amount)->toEqual(2.0)
        ->and($recipeOliveOil->pivot->unit)->toBe('tbsp')
        ->and($recipeOliveOil->pivot->description)->toBe('extra virgin olive oil');

    // Find garlic ingredient and check its pivot data
    $recipeGarlic = $recipe->ingredients->firstWhere('name', 'garlic');
    expect($recipeGarlic)->not->toBeNull()
        ->and($recipeGarlic->pivot->amount)->toEqual(3.0)
        ->and($recipeGarlic->pivot->unit)->toBe('clove')
        ->and($recipeGarlic->pivot->description)->toBe('garlic, minced');
});

it('skips ingredients with empty base names', function () {
    $user = createUser();
    Auth::login($user);

    // Normalized ingredient data with one valid ingredient and one with empty base_name
    $normalizedIngredients = [
        [
            'base_name' => 'salt',
            'quantity' => 0,
            'unit' => 'pinch',
            'preparation_notes' => 'to taste',
            'description' => 'salt, to taste',
            'original_string' => 'salt to taste'
        ],
        [
            'base_name' => '', // Empty base name
            'quantity' => 0,
            'unit' => null,
            'preparation_notes' => null,
            'description' => 'to serve',
            'original_string' => 'to serve'
        ]
    ];

    // Mock the normalization service
    $normalizationService = mock(IngredientNormalizationService::class);
    $normalizationService->shouldReceive('normalize')
        ->with(['salt to taste', 'to serve'])
        ->andReturn($normalizedIngredients);

    $parser = new RecipeParser(normalizationService: $normalizationService);

    // Create salt ingredient in the database
    $salt = Ingredient::factory()->create(['name' => 'salt']);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'name' => ['Test Recipe'],
            'recipeIngredient' => [
                'salt to taste',
                'to serve'
            ],
        ]);

    $recipe = $parser->parse($item);

    // Verify only the valid ingredient was attached
    expect($recipe->ingredients)->toHaveCount(1);

    // Check salt was properly attached with description
    $recipeSalt = $recipe->ingredients->first();
    expect($recipeSalt->name)->toBe('salt')
        ->and($recipeSalt->pivot->unit)->toBe('pinch')
        ->and($recipeSalt->pivot->description)->toBe('salt, to taste');
});

it('uses fallback values for missing fields in normalized data', function () {
    $user = createUser();
    Auth::login($user);

    // Normalized ingredient data with some missing fields
    $normalizedIngredients = [
        [
            'base_name' => 'sugar',
            'quantity' => 0,
            // Missing unit
            // Missing preparation_notes
            // Missing description
            'original_string' => 'sugar'
        ],
        [
            'base_name' => 'water',
            'quantity' => 1,
            // Missing unit
            // Missing preparation_notes
            'description' => 'water', // Has description
            'original_string' => '1 cup water'
        ]
    ];

    // Mock the normalization service
    $normalizationService = mock(IngredientNormalizationService::class);
    $normalizationService->shouldReceive('normalize')
        ->with(['sugar', '1 cup water'])
        ->andReturn($normalizedIngredients);

    $parser = new RecipeParser(normalizationService: $normalizationService);

    // Create ingredients in the database
    $sugar = Ingredient::factory()->create(['name' => 'sugar']);
    $water = Ingredient::factory()->create(['name' => 'water']);

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'name' => ['Test Recipe'],
            'recipeIngredient' => [
                'sugar',
                '1 cup water'
            ],
        ]);

    $recipe = $parser->parse($item);

    // Verify ingredients were attached
    expect($recipe->ingredients)->toHaveCount(2);

    // Check sugar has fallback values
    $recipeSugar = $recipe->ingredients->firstWhere('name', 'sugar');
    expect($recipeSugar)->not->toBeNull()
        ->and($recipeSugar->pivot->amount)->toEqual(0.0)
        ->and($recipeSugar->pivot->unit)->toBeNull()
        ->and($recipeSugar->pivot->description)->toBe('sugar'); // Falls back to original_string or base_name

    // Check water has the correct values
    $recipeWater = $recipe->ingredients->firstWhere('name', 'water');
    expect($recipeWater)->not->toBeNull()
        ->and($recipeWater->pivot->amount)->toEqual(1.0)
        ->and($recipeWater->pivot->unit)->toBeNull()
        ->and($recipeWater->pivot->description)->toBe('water');
});

it('normalizes recipe instructions using InstructionNormalizationService', function () {
    $user = createUser();
    Auth::login($user);

    // Create a custom mock for InstructionNormalizationService for this test
    $instructionNormalizationService = mock(InstructionNormalizationService::class);
    $instructionNormalizationService->shouldReceive('normalize')
        ->withArgs(function ($rawInstructions) {
            // Verify the raw instructions are passed as expected
            return $rawInstructions === "Step 1\nStep 2\nStep 3";
        })
        ->andReturn('1. **Step 1**\n2. **Step 2**\n3. **Step 3**');

    // Create parser with the custom mock
    $parser = new RecipeParser(
        normalizationService: mock(IngredientNormalizationService::class)->shouldReceive('normalize')->andReturn([])->getMock(),
        instructionNormalizationService: $instructionNormalizationService
    );

    $item = mock(Item::class);
    $item->shouldReceive('getProperties')
        ->andReturn([
            'name' => ['Test Recipe'],
            'recipeInstructions' => ['Step 1', 'Step 2', 'Step 3']
        ]);

    $recipe = $parser->parse($item);

    expect($recipe->instructions)
        ->toBe('1. **Step 1**\n2. **Step 2**\n3. **Step 3**');
});
