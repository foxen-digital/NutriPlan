# Feature: Instruction Normalization Service

**Version:** 1.1
**Status:** Planned
**Date:** {{ CURRENT_DATE }}

**Related Feature:** [Instruction Normalization](feature-instruction-normalization.md)

## 1. Overview

This document details the requirements and design for the `InstructionNormalizationService`, a component of the larger Instruction Normalization feature. This service is responsible for interacting with an LLM to parse and format raw recipe instruction strings into Markdown.

## 2. Goals

-   Provide a reusable service to normalize recipe instructions using an LLM.
-   Ensure consistent Markdown formatting for instructions.
-   Implement robust error handling and fallback mechanisms for LLM interactions.

## 3. Requirements

-   A new service `App\Services\InstructionNormalizationService` will be created.
-   It will utilize `App\Services\Clients\OpenAiClient` to communicate with an LLM (e.g., GPT-4o Mini).
-   **Input:** A single string containing the raw instructions (e.g., concatenated with double newlines `\n\n`).
-   **Output:** A single string containing the instructions formatted directly as Markdown (e.g., a numbered list).
-   **LLM Interaction:**
    -   A system prompt will instruct the LLM to parse the input string and return a single Markdown string representing the steps.
-   **Error Handling:** Implement retry logic (exponential backoff) for LLM API calls.
-   **Fallback:** If the LLM fails after retries or returns an empty/unusable response:
    -   Log the error.
    -   Return the original input instruction string.

## 4. Technical Design

-   **Structure:** Similar to `IngredientNormalizationService`, including constructor injection of `OpenAiClient`, a public `normalize(string $rawInstructions): string` method, and private helper methods for:
    -   Building the prompt.
    -   Making LLM API calls (with retries).
    -   Validating the response (e.g., ensuring it's not empty).
    -   Implementing the fallback logic.
-   **LLM Prompt:**
    ```
    You are a helpful assistant that converts cooking and recipe instructions into clean, structured Markdown format.

    Your task is to improve readability and formatting without changing the content or order of steps.

    Formatting guidelines:
    - Use numbered lists for sequential instructions.
    - Use bold (`**...**`) to highlight important ingredients, actions, and timings (e.g., **onions**, **10 minutes**, **cover and cook**).
    - Use italics (`*...*`) for optional notes or helpful tips (e.g., *if desired*, *note: do not overmix*).
    - Only include headings (e.g., `## Slow Cooker Directions`) if they are present in the input or clearly indicated by context (such as multiple preparation methods). Do not add generic headings like `## Instructions`.
    - Do not add or remove any steps, ingredients, or instructions unless explicitly told to.
    - Do not include ingredient lists unless they are part of the input.
    - Return only the cleaned-up Markdown-formatted version of the instructions, with no extra explanation.

    Your goal is to make the instructions easier to read and use, while preserving their original meaning and tone.
    ```

## 5. Implementation Plan

1.  Implement the `InstructionNormalizationService` class structure with necessary methods.
2.  Implement the prompt building logic (taking a single string input).
3.  Implement the LLM API call logic with retries using `OpenAiClient`, expecting a direct string response.
4.  Implement response validation (e.g., check if empty).
5.  Implement the fallback logic (return original input string).
6.  Write unit/feature tests covering successful normalization, error handling, and fallback scenarios.

## 6. Future Considerations

-   Could the fallback mechanism attempt basic Markdown conversion (e.g., adding `1. ` prefix to lines)? Potentially, but returning the original is safest initially.
-   Parameterizing the LLM model used.
-   Handling cases where the LLM might still include preamble/postamble text despite instructions. 