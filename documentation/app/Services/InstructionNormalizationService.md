# Documentation: InstructionNormalizationService.php

Original file: `app/Services/InstructionNormalizationService.php`

# InstructionNormalizationService Documentation

## Table of Contents
- [Introduction](#introduction)
- [Constructor](#constructor)
- [Methods](#methods)
  - [normalize](#normalize)
  - [normalizeWithLlm](#normalizewithllm)
  - [buildPrompt](#buildprompt)
  - [makeLlmRequest](#make_llm_request)
  - [normalizeWithFallback](#normalizewithfallback)

## Introduction
`InstructionNormalizationService.php` is a PHP class responsible for normalizing cooking and recipe instructions into a clean, structured Markdown format. This service leverages the capabilities of an external AI service, specifically the OpenAI API, to enhance the readability and formatting of recipe instructions while ensuring the original instructions' content and order are maintained. The primary role of this class is to encapsulate the logic for interacting with the AI service and provide fallback mechanisms in scenarios where the AI fails to respond appropriately.

## Constructor

### `__construct(OpenAiClient $openAiClient)`

- **Purpose**: Initializes an instance of the `InstructionNormalizationService` class.
- **Parameters**:
  - `OpenAiClient $openAiClient`: An instance of `OpenAiClient` used to communicate with the OpenAI API.
- **Return Value**: None
- **Functionality**: The constructor utilizes Dependency Injection to inject the OpenAI client into the service, ensuring that the service has access to the necessary resources for operation.

## Methods

### `normalize(string $rawInstructions): string`

- **Purpose**: Normalizes the given raw instructions into a clean Markdown format.
- **Parameters**:
  - `string $rawInstructions`: The raw cooking instructions input that needs normalization.
- **Return Value**: Returns a string containing the normalized Markdown-formatted instructions.
- **Functionality**: 
  1. Validates the input to ensure it is not empty or equal to '0'.
  2. Attempts to normalize the instructions using the AI service.
  3. Logs the length of the normalized instructions.
  4. If normalization fails, it falls back to returning the raw instructions.
  
  ```php
  public function normalize(string $rawInstructions): string {
      // Implementation...
  }
  ```
  
### `normalizeWithLlm(string $rawInstructions): string`

- **Purpose**: Normalizes cooking instructions using the language model (LLM) API.
- **Parameters**:
  - `string $rawInstructions`: The raw instructions to normalize.
- **Return Value**: Returns a string containing the AI-normalized instructions.
- **Functionality**:
  1. Builds a prompt for the AI model using the raw instructions.
  2. Sends the prompt to the AI service and retrieves the response.
  3. Checks the response format for validity, extracting the content if present.
  4. Throws exceptions if the response is not as expected or empty.
  
  ```php
  private function normalizeWithLlm(string $rawInstructions): string {
      // Implementation...
  }
  ```

### `buildPrompt(string $rawInstructions): array`

- **Purpose**: Constructs a prompt for the language model.
- **Parameters**:
  - `string $rawInstructions`: The raw instructions to be formatted.
- **Return Value**: Returns an array containing the structured prompt needed for the AI model.
- **Functionality**: 
  - Creates a prompt structured according to the system requirements for the AI model, including the system role's instruction and the user's raw instructions.
  
  ```php
  private function buildPrompt(string $rawInstructions): array {
      // Implementation...
  }
  ```

### `makeLlmRequest(array $prompt, int $attempt = 1): Response`

- **Purpose**: Handles making the request to the OpenAI service to get the normalized instructions.
- **Parameters**:
  - `array $prompt`: The prompt delivered to the AI service.
  - `int $attempt`: The current attempt number (default is 1).
- **Return Value**: Returns a `Response` object from the OpenAI client.
- **Functionality**:
  1. Utilizes exponential backoff in case of failures to communicate with the API (retries up to three times).
  2. Checks if the response is successful and returns it; otherwise, it tries again or throws an exception if maximum attempts are reached.
  
  ```php
  private function makeLlmRequest(array $prompt, int $attempt = 1): Response {
      // Implementation...
  }
  ```

### `normalizeWithFallback(string $rawInstructions): string`

- **Purpose**: Provides a fallback mechanism when normalization fails.
- **Parameters**:
  - `string $rawInstructions`: The raw instructions to return in case of failure.
- **Return Value**: Simply returns the raw instructions.
- **Functionality**: 
  - Logs a warning indicating that the fallback normalization is in use and returns the unmodified raw instructions.
  
  ```php
  private function normalizeWithFallback(string $rawInstructions): string {
      // Implementation...
  }
  ```

## Conclusion
The `InstructionNormalizationService` is a robust PHP service designed to efficiently format cooking instructions into structured Markdown through the use of AI technology. Its design promotes separation of concerns, error handling, and logging for better maintainability and usability within the broader application ecosystem.