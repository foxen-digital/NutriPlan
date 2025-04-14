<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\FetchRecipe;
use App\Exceptions\RecipeImport\ConnectionFailedException;
use App\Exceptions\RecipeImport\NoStructuredDataException;
use App\Models\Recipe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportRecipeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param string $url The URL of the recipe to import
     * @param int $userId The ID of the user initiating the import
     */
    public function __construct(
        public readonly string $url,
        public readonly int $userId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(FetchRecipe $action): ?Recipe
    {
        try {
            // Set the auth context for the job
            auth()->loginUsingId($this->userId);

            // Fetch and parse the recipe
            $recipe = $action->handle($this->url);

            // Log success
            Log::info('Recipe imported successfully', [
                'url' => $this->url,
                'recipe_id' => $recipe->id,
                'recipe_title' => $recipe->title,
                'user_id' => $this->userId
            ]);

            return $recipe;
        } catch (NoStructuredDataException $e) {
            Log::warning('No recipe data found during import', [
                'url' => $this->url,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (ConnectionFailedException $e) {
            Log::warning('Connection failed during recipe import', [
                'url' => $this->url,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (Throwable $e) {
            Log::error('Recipe import failed', [
                'url' => $this->url,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            // Clean up the auth context
            auth()->logout();
        }
    }
}
