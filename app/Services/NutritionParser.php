<?php

namespace App\Services;

use Brick\StructuredData\Item;
use Illuminate\Support\Str;

class NutritionParser
{
    /**
     * Parse nutrition information from structured data
     *
     * @param array<int, Item|string|array> $values
     * @return array<string, string>|null
     */
    public function parse(array $values): ?array
    {
        $nutritionData = [];

        foreach ($values as $item) {
            // Handle Item objects (from structured data parsers)
            if ($item instanceof Item) {
                // Check if this is a nutrition information item
                $isNutritionItem = false;
                $types = $item->getTypes();
                foreach ($types as $type) {
                    if (Str::contains(Str::lower($type), 'nutrition')) {
                        $isNutritionItem = true;
                        break;
                    }
                }

                foreach ($item->getProperties() as $name => $propValues) {
                    $propertyName = Str::replace(['http://schema.org/', 'https://schema.org/'], '', Str::lower($name));

                    // Map schema.org property names to our database column names
                    $mappedName = $this->mapPropertyName($propertyName);

                    if ($mappedName && !empty($propValues)) {
                        // @phpstan-ignore-next-line - Safety check for array type
                        $value = is_array($propValues) ? $propValues[0] : $propValues;
                        $nutritionData[$mappedName] = $this->cleanNutritionValue($mappedName, html_entity_decode((string) $value));
                    }
                }

                // If this is a nutrition item and we found data, use it
                if ($isNutritionItem && $nutritionData !== []) {
                    return $nutritionData;
                }
            }
            // Handle array data (from JSON-LD)
            elseif (is_array($item)) {
                // Check if this is a nutrition information item
                $isNutritionItem = false;
                if (isset($item['@type'])) {
                    $isNutritionItem = Str::contains(Str::lower($item['@type']), 'nutrition');
                }

                // Handle direct array of nutrition data
                foreach ($item as $key => $value) {
                    if ($key === '@type') {
                        continue;
                    }
                    if ($key === '@context') {
                        continue;
                    }
                    $propertyName = Str::lower($key);
                    $mappedName = $this->mapPropertyName($propertyName);

                    if ($mappedName && !empty($value)) {
                        $cleanValue = is_array($value) ? html_entity_decode((string) $value[0]) : html_entity_decode((string) $value);
                        $nutritionData[$mappedName] = $this->cleanNutritionValue($mappedName, $cleanValue);
                    }
                }

                // Check for nested nutrition object
                if (isset($item['nutrition']) && is_array($item['nutrition'])) {
                    foreach ($item['nutrition'] as $key => $value) {
                        if ($key === '@type') {
                            continue;
                        }
                        if ($key === '@context') {
                            continue;
                        }
                        $propertyName = Str::lower($key);
                        $mappedName = $this->mapPropertyName($propertyName);

                        if ($mappedName && !empty($value)) {
                            $cleanValue = is_array($value) ? html_entity_decode((string) $value[0]) : html_entity_decode((string) $value);
                            $nutritionData[$mappedName] = $this->cleanNutritionValue($mappedName, $cleanValue);
                        }
                    }
                }

                // If this is a nutrition item or we found data, use it
                if (($isNutritionItem || isset($item['nutrition'])) && $nutritionData !== []) {
                    return $nutritionData;
                }
            }
        }

        // If we found any nutrition data, return it
        return $nutritionData !== [] ? $nutritionData : null;
    }

    /**
     * Map schema.org property names to our database column names
     */
    private function mapPropertyName(string $propertyName): ?string
    {
        return match ($propertyName) {
            'calories' => 'calories',
            'carbohydratecontent' => 'carbohydrate_content',
            'cholesterolcontent' => 'cholesterol_content',
            'fatcontent' => 'fat_content',
            'fibercontent' => 'fiber_content',
            'proteincontent' => 'protein_content',
            'saturatedfatcontent' => 'saturated_fat_content',
            'servingsize' => 'serving_size',
            'sodiumcontent' => 'sodium_content',
            'sugarcontent' => 'sugar_content',
            'transfatcontent' => 'trans_fat_content',
            'unsaturatedfatcontent' => 'unsaturated_fat_content',
            default => null,
        };
    }

    /**
     * Cleans up nutrition values by removing redundant information
     */
    private function cleanNutritionValue(string $type, string $value): string
    {
        // Define patterns to clean up for each nutrition type
        $patterns = [
            'calories' => ['/\s*calories\s*$/i'],
            'carbohydrate_content' => ['/\s*(grams?|g)\s*(carbohydrates?|carbs?)\s*$/i', '/\s*carbohydrates?\s*$/i'],
            'protein_content' => ['/\s*(grams?|g)\s*protein\s*$/i', '/\s*protein\s*$/i'],
            'fat_content' => ['/\s*(grams?|g)\s*fat\s*$/i', '/\s*fat\s*$/i'],
            'fiber_content' => ['/\s*(grams?|g)\s*fiber\s*$/i', '/\s*fiber\s*$/i'],
            'sugar_content' => ['/\s*(grams?|g)\s*sugar\s*$/i', '/\s*sugar\s*$/i'],
            'sodium_content' => ['/\s*(milligrams?|mg)\s*of\s*sodium\s*$/i', '/\s*sodium\s*$/i'],
            'saturated_fat_content' => ['/\s*(grams?|g)\s*saturated\s*fat\s*$/i', '/\s*saturated\s*fat\s*$/i'],
            'trans_fat_content' => ['/\s*(grams?|g)\s*trans\s*fat\s*$/i', '/\s*trans\s*fat\s*$/i'],
            'unsaturated_fat_content' => ['/\s*(grams?|g)\s*unsaturated\s*fat\s*$/i', '/\s*unsaturated\s*fat\s*$/i'],
            'cholesterol_content' => ['/\s*(milligrams?|mg)\s*cholesterol\s*$/i', '/\s*cholesterol\s*$/i'],
        ];

        // Apply patterns if they exist for this nutrition type
        if (isset($patterns[$type])) {
            foreach ($patterns[$type] as $pattern) {
                $value = preg_replace($pattern, '', (string) $value);
            }

            // Trim any remaining whitespace
            $value = trim((string) $value);

            // Add units if they're missing
            if (is_numeric($value)) {
                $value = match ($type) {
                    'calories' => $value . ' cal',
                    'sodium_content', 'cholesterol_content' => $value . ' mg',
                    default => $value . ' g',
                };
            }
        }

        return $value;
    }
}
