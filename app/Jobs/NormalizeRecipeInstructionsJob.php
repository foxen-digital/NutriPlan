<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\RecipeInstructionsNormalized;
use App\Models\Recipe;
use App\Services\InstructionNormalizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class NormalizeRecipeInstructionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [5, 15, 30];

    /**
     * Create a new job instance.
     *
     * @param int $recipeId The ID of the recipe whose instructions to normalize
     * @param int $userId The ID of the user to notify when normalization completes
     */
    public function __construct(
        public readonly int $recipeId,
        public readonly int $userId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(InstructionNormalizationService $service): void
    {
        try {
            // Set the auth context for the job
            auth()->loginUsingId($this->userId);

            $recipe = Recipe::find($this->recipeId);

            if ($recipe === null) {
                // Recipe deleted between dispatch and processing - skip silently
                Log::info('Instruction normalization skipped - recipe not found', [
                    'recipe_id' => $this->recipeId,
                    'user_id' => $this->userId,
                ]);

                return;
            }

            $raw = (string) $recipe->instructions;

            // Mirror the service's own degenerate-input guard: nothing to format,
            // so skip without an event or a database write.
            if ($raw === '' || $raw === '0') {
                Log::info('Instruction normalization skipped - nothing to normalize', [
                    'recipe_id' => $recipe->id,
                    'user_id' => $this->userId,
                ]);

                return;
            }

            $normalized = $service->normalize($raw);

            // The service never throws for LLM failures; it returns the raw input
            // as fallback. Output identical to input therefore means fallback.
            if ($normalized === $raw) {
                Log::warning('Instruction normalization fell back to raw input', [
                    'recipe_id' => $recipe->id,
                    'user_id' => $this->userId,
                ]);

                RecipeInstructionsNormalized::dispatch(
                    $this->userId,
                    'warning',
                    "We couldn't auto-format the instructions for '{$recipe->title}'. The original instructions were kept.",
                    $recipe
                );

                return;
            }

            $recipe->update(['instructions' => $normalized]);

            Log::info('Recipe instructions normalized', [
                'recipe_id' => $recipe->id,
                'user_id' => $this->userId,
            ]);

            RecipeInstructionsNormalized::dispatch(
                $this->userId,
                'success',
                "Instructions for '{$recipe->title}' have been formatted.",
                $recipe
            );
        } catch (Throwable $e) {
            Log::error('Instruction normalization failed', [
                'recipe_id' => $this->recipeId,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Dispatch event for real-time notification
            RecipeInstructionsNormalized::dispatch(
                $this->userId,
                'error',
                'We could not format the instructions for this recipe. The original instructions were kept.',
                null
            );

            throw $e;
        } finally {
            // Clean up the auth context
            auth()->logout();
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Instruction normalization job failed permanently', [
            'recipe_id' => $this->recipeId,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
