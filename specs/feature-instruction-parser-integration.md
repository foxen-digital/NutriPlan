# Feature: Recipe Parser Integration for Instruction Normalization

**Version:** 1.0
**Status:** Planned
**Date:** {{ CURRENT_DATE }}

**Related Features:** 
- [Instruction Normalization](feature-instruction-normalization.md)
- [Instruction Normalization Service](feature-instruction-normalization-service.md)

## 1. Overview

This document outlines the necessary changes to `App\Services\RecipeParser` to integrate the `InstructionNormalizationService`. This integration will replace the current simple concatenation of instruction steps with the LLM-powered Markdown normalization.

## 2. Goals

-   Modify `RecipeParser` to utilize the new `InstructionNormalizationService`.
-   Ensure the normalized Markdown instructions are correctly saved to the `Recipe` model.
-   Decouple instruction formatting logic from the `RecipeParser`.

## 3. Requirements

-   `App\Services\RecipeParser` must be updated.
-   It needs to depend on and utilize `App\Services\InstructionNormalizationService`.
-   The `parse` method within `RecipeParser` should call `InstructionNormalizationService::normalize()` with the extracted raw instruction steps (`$this->steps`).
-   The Markdown string returned by the `normalize` method must be saved into the `instructions` column of the `recipes` table for the corresponding `Recipe` model.
-   The dependency on `Auth::id()` within `RecipeParser` must be addressed later as part of the Asynchronous Recipe Import feature. For this specific integration step, constructor injection of the service is the primary focus.

## 4. Technical Design

-   **Dependency Injection:** Inject `InstructionNormalizationService` into the constructor of `RecipeParser`.
-   **Method Modification:** Modify the `parse` method:
    -   Locate the line where `$this->steps` are currently processed (e.g., `implode("\n\n", $this->steps)`).
    -   Replace this logic with a call to the injected service: `$normalizedInstructions = $this->instructionNormalizationService->normalize(implode("\n", $this->steps)`);`
    -   Ensure `$normalizedInstructions` is assigned to the `instructions` property of the `Recipe` model before saving (e.g., `$recipe->instructions = $normalizedInstructions;`).

## 5. Implementation Plan

1.  Add `InstructionNormalizationService` as a dependency to `RecipeParser`'s constructor.
2.  Update the `parse` method to call `InstructionNormalizationService::normalize()`.
3.  Ensure the returned value is assigned to the `Recipe` model's `instructions` attribute.
4.  Update any relevant unit or feature tests for `RecipeParser` to mock the `InstructionNormalizationService` and verify it's called correctly and the result is used.

## 6. Future Considerations

-   How to handle potential `null` or empty string returns from the normalization service (though the service spec includes a fallback, defensive coding in the parser might be wise). 