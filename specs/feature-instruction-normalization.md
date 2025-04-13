# Feature: Instruction Normalization

**Version:** 1.0
**Status:** Planned
**Date:** {{ CURRENT_DATE }}

## 1. Overview

This document outlines the plan to implement an instruction normalization feature, similar to the existing ingredient normalization. This involves using an LLM to parse and format recipe instructions into Markdown, refactoring the recipe import process into a queued job, and providing real-time feedback to the user via WebSockets (Laravel Reverb).

## 2. Goals

-   Normalize recipe instructions into a consistent Markdown format for better display and potential future processing.
-   Improve user experience by making the recipe import process asynchronous, preventing UI blocking.
-   Provide real-time feedback to the user about the status of their recipe import.
-   Leverage existing infrastructure (`OpenAiClient`, Notification component).

## 3. Requirements

This feature is broken down into the following sub-features, each with its own specification document:

1.  **Instruction Normalization Service:** Creates the core service for LLM-based instruction parsing.
    -   See: [feature-instruction-normalization-service.md](feature-instruction-normalization-service.md)
2.  **Recipe Parser Integration:** Integrates the new service into the existing recipe parser.
    -   See: [feature-instruction-parser-integration.md](feature-instruction-parser-integration.md)
3.  **Asynchronous Recipe Import:** Refactors the import process into a queued job.
    -   See: [feature-async-recipe-import.md](feature-async-recipe-import.md)
4.  **Real-time Frontend Notifications:** Implements WebSocket notifications for import status.
    -   See: [feature-real-time-notifications.md](feature-real-time-notifications.md)

## 4. Technical Design

-   **`InstructionNormalizationService`:** Structure similar to `IngredientNormalizationService`, including constructor injection of `OpenAiClient`, `normalize` method, private helper methods for prompt building, LLM calls, JSON parsing, validation, and fallback logic.
-   **`ImportRecipeJob`:** Standard Laravel job structure. May require creating a small helper service/action to encapsulate the URL fetching and `RecipeParser` invocation logic to keep the job's `handle` method clean and testable, passing the `userId` explicitly.
-   **Event Broadcasting:** Standard Laravel event broadcasting setup using Reverb.
-   **Frontend Listening:** Standard Laravel Echo setup for private channels.

## 5. Implementation Plan

1.  **(Backend)** Implement `InstructionNormalizationService` with LLM integration and fallback.
2.  **(Backend)** Write unit/feature tests for `InstructionNormalizationService`.
3.  **(Backend)** Inject and use `InstructionNormalizationService` within `RecipeParser`. Update `RecipeParser` tests if necessary.
4.  **(Backend)** Create `ImportRecipeJob` and move import logic into it. Refactor import trigger point.
5.  **(Backend)** Create `RecipeImportCompleted` event and integrate broadcasting into `ImportRecipeJob`.
6.  **(Backend)** Configure Reverb if not already done. Ensure necessary broadcasting routes are uncommented/added in `routes/channels.php`.
7.  **(Frontend)** Implement Echo listener for `RecipeImportCompleted` event and integrate with the notification component.
8.  **(Testing)** Write feature tests for the asynchronous import process and notification flow.

## 6. Future Considerations

-   Allow users to edit the normalized Markdown instructions.
-   Provide a way to re-normalize instructions for existing recipes.
-   Explore more advanced parsing (e.g., extracting timings or equipment from instructions).
-   Could the fallback mechanism attempt basic Markdown conversion (e.g., adding `1. ` prefix)? Potentially, but simple concatenation is safer for now. 