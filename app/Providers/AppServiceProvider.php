<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\MealAssignment;
use App\Observers\MealAssignmentObserver;
use App\Services\Clients\OpenAiClient;
use App\Services\IngredientNormalizationService;
use App\Services\IngredientParser;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OpenAiClient::class, fn (Container $app): \App\Services\Clients\OpenAiClient => new OpenAiClient(
            config('services.openai.api_key'),
            config('services.openai.model', 'gpt-4o-mini')
        ));

        $this->app->singleton(IngredientNormalizationService::class, fn (Container $app): \App\Services\IngredientNormalizationService => new IngredientNormalizationService(
            $app->make(OpenAiClient::class),
            $app->make(IngredientParser::class)
        ));
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
        Model::preventLazyLoading(!$this->app->isProduction());

        JsonResource::withoutWrapping();
    }
}
