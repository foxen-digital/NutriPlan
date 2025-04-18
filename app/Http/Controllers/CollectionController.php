<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use App\Actions\CreateCollectionAction;
use App\Http\Requests\CreateCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CollectionController extends Controller
{
    public function index(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $collections = Collection::query()
            ->where('user_id', auth()->id())
            ->withCount('recipes')
            ->latest()
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'collections' => $collections,
            ]);
        }

        return Inertia::render('Collections/Index', [
            'collections' => $collections,
        ]);
    }

    public function show(Collection $collection): Response
    {
        Gate::authorize('view', $collection);

        $collection->load(['recipes.categories' => function (BelongsToMany $query): void {
            $query->latest();
        }, 'recipes.user']);

        return Inertia::render('Collections/Show', [
            'collection' => $collection,
        ]);
    }

    public function store(CreateCollectionRequest $request, CreateCollectionAction $action): RedirectResponse
    {
        $user = $request->user();
        $action->handle($user, $request->validated());

        return redirect()->route('collections.index')
            ->with('success', 'Collection created successfully.');
    }

    public function update(UpdateCollectionRequest $request, Collection $collection): RedirectResponse
    {
        $collection->update($request->validated());

        return redirect()->route('collections.index')
            ->with('success', 'Collection updated successfully.');
    }

    public function destroy(Collection $collection): RedirectResponse
    {
        Gate::authorize('delete', $collection);

        $collection->delete();

        return redirect()->route('collections.index')
            ->with('success', 'Collection deleted successfully.');
    }
}
