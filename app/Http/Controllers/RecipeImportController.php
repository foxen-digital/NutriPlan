<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ImportRecipeJob;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Recipe\ImportRecipeRequest;

class RecipeImportController extends Controller
{
    /**
     * Queue a recipe import from a URL.
     *
     * This controller handles the process of dispatching a job to asynchronously
     * import a recipe from an external website.
     *
     * @param ImportRecipeRequest $request The validated request containing the recipe URL
     */
    public function __invoke(ImportRecipeRequest $request): RedirectResponse
    {
        // Dispatch the job to import the recipe asynchronously
        ImportRecipeJob::dispatch(
            $request->input('url'),
            auth()->id()
        );

        // Return an immediate response to the user
        return back()->with('success', 'Recipe queued for import. You will be notified when it completes.');
    }
}
