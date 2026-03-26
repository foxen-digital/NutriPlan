<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\MealAssignment;
use App\Models\User;
use App\Observers\MealAssignmentObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        MealAssignment::observe(MealAssignmentObserver::class);

        // No mass assignment protection at all.
        Model::unguard();

        // As these are concerned with application correctness,
        // leave them enabled all the time.
        Model::preventAccessingMissingAttributes();
        Model::preventSilentlyDiscardingAttributes();

        // Since this is a performance concern only, don't halt
        // production for violations.
        Model::preventLazyLoading(! $this->app->isProduction());

        JsonResource::withoutWrapping();

        Gate::define('viewPulse', function (User $user) {
            return $user->isAdmin();
        });
    }
}
