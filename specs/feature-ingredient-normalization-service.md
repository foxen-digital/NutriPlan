# Feature: Ingredient Normalization Service

## Overview
This specification details the creation of the `IngredientNormalizationService`, a dedicated service responsible for parsing and normalizing raw ingredient strings using an external Large Language Model (LLM) API. This service aims to extract structured information (base name, quantity, unit, preparation notes) from unstructured text, handle potential API errors, and provide a fallback mechanism using the existing `IngredientParser`.

## Depends On
- Basic Laravel Application Structure
- Laravel HTTP Client (`Illuminate\Support\Facades\Http`)
- Laravel Logging (`Illuminate\Support\Facades\Log`)
- Laravel Configuration (`Illuminate\Support\Facades\Config`)
- Existing `App\Services\IngredientParser` (for fallback)

## Leads To
- Integration of this service into the recipe import process (detailed in a separate spec).

## Core Functionality
- Accept an array of raw ingredient strings.
- Interact with a configured external LLM API to parse each string.
- Extract structured data: `base_name`, `quantity`, `unit`, `preparation_notes`, `description`, `original_string`.
- Implement robust error handling, including retries and fallback to the existing `IngredientParser`.
- Return a consistent array of structured ingredient data, even in cases of partial failure (using fallback data or nulls).

## Implementation Details

### Service Definition

#### IngredientNormalizationService (`App\Services\IngredientNormalizationService`)
- **Purpose:** Encapsulates interaction with the external LLM API for ingredient parsing.
- **Methods:**
    - `public function normalize(array $ingredientStrings): array`
        - Takes an array of raw ingredient strings.
        - Iterates through the strings or prepares a batch request for the LLM.
        - For each string (or batch):
            - Constructs the prompt for the LLM (see LLM Prompt Structure section).
            - Makes the API call to the chosen LLM endpoint (handle authentication securely via config/env).
            - **Error Handling:** Implements error handling for the API call:
                - **Retry Logic:** Retry the API call 1-2 times with exponential backoff on failures (e.g., 5xx errors, timeouts).
                - **Fallback Logic:** If the LLM call ultimately fails after retries, attempt to parse the specific problematic string(s) using the *existing* `App\Services\IngredientParser`. Log a warning indicating fallback usage.
                - **Response Validation:** Validate the LLM JSON response structure. If invalid or doesn't conform to the expected format, log an error and attempt fallback for the affected string(s).
            - Parses the successful LLM JSON response.
            - If fallback was used, transform the output of `IngredientParser::parse()` into the standardized structured format as best as possible (e.g., `base_name` might be the less clean name, `preparation_notes` might be null).
            - Collects the structured data for each ingredient string.
        - Returns an array of structured ingredient data objects/arrays, maintaining the order corresponding to the input strings.
- **Dependencies:**
    - `Illuminate\Support\Facades\Http`
    - `Illuminate\Support\Facades\Log`
    - `Illuminate\Support\Facades\Config`
    - `App\Services\IngredientParser`

### Configuration
- Add necessary configuration for the chosen LLM API key and endpoint URL in `.env` and `config/services.php`.
    - Example `config/services.php` entry:
    ```php
    'llm' => [
        'ingredient_parser' => [
            'endpoint' => env('LLM_INGREDIENT_ENDPOINT'),
            'key' => env('LLM_API_KEY'),
            // Add other necessary parameters like model name if needed
        ],
    ],
    ```
- Define corresponding `LLM_INGREDIENT_ENDPOINT` and `LLM_API_KEY` in `.env`.

## LLM Prompt Structure (Example)

```json
{
  "prompt": "Given the following list of ingredient strings from a recipe:\n[\n  \"2 red onions, peeled and quartered\",\n  \"1 tbsp olive oil\",\n  \"salt and pepper to taste\"\n]\n\nPlease return a JSON array where each object represents an ingredient and has the following keys:\n- \"base_name\": The common name of the ingredient (e.g., \"red onion\", \"olive oil\", \"salt\").\n- \"quantity\": The numeric quantity (e.g., 2, 1). Use 0 or null if not applicable (like \"to taste\").\n- \"unit\": The unit of measurement (e.g., \"piece\", \"tbsp\", \"pinch\"). Use standard abbreviations or full names. Use null or \"unit\" if not applicable.\n- \"preparation_notes\": Any preparation instructions or extra details present in the original string (e.g., \"peeled and quartered\", \"to taste\"). Use null if none.\n- \"description\": The base_name & preparation_notes combined.\n- \"original_string\": The full original ingredient string.\n\nEnsure the output is valid JSON.",
  "parameters": {
     // LLM specific parameters like temperature, max_tokens etc.
  }
}
```
*Note: The exact prompt structure might need adjustment based on the specific LLM API requirements.* 

## Testing Strategy

### Unit Tests (`tests/Unit/Services/IngredientNormalizationServiceTest.php`)
- Test the `normalize` method extensively.
- **Mocking:**
    - Mock the `Http` facade to simulate various API responses (success with valid JSON, success with invalid JSON, 5xx errors, timeouts).
    - Mock the `IngredientParser` to control its behavior during fallback tests.
    - Mock `Log` facade to verify warnings/errors are logged appropriately.
- **Assertions:**
    - Verify correct prompt construction based on input strings.
    - Test successful parsing and structuring of valid LLM JSON responses.
    - Test retry logic is triggered on simulated transient failures.
    - Test fallback logic is triggered on simulated persistent LLM failures or invalid responses.
    - Verify the structure of the data returned when the fallback parser is used.
    - Test handling of completely empty input arrays.
    - Test handling of edge-case strings (e.g., only quantity, only name, complex descriptions) by simulating appropriate LLM responses or fallback scenarios.
    - Verify logs are written for failures and fallbacks.

## Future Considerations
- **LLM Choice & Cost Management:** This will be an ongoing concern, potentially requiring adjustments to the service or configuration based on real-world usage.
- **Batching:** If the chosen LLM API supports efficient batch processing of multiple prompts, the service could be optimized to make fewer API calls.
- **Fine-tuning/Prompt Engineering:** The initial prompt is a starting point; refinement might be needed for better accuracy based on observed LLM performance. 