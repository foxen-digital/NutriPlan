<?php

namespace App\Providers;

use App\Services\Clients\OpenAiClient;
use App\Services\IngredientNormalizationService;
use App\Services\IngredientParser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Contracts\Container\Container;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OpenAiClient::class, fn(Container $app): \App\Services\Clients\OpenAiClient => new OpenAiClient(
            config('services.openai.api_key'),
            config('services.openai.model', 'gpt-4o-mini')
        ));

        $this->app->singleton(IngredientNormalizationService::class, fn(Container $app): \App\Services\IngredientNormalizationService => new IngredientNormalizationService(
            $app->make(OpenAiClient::class),
            $app->make(IngredientParser::class)
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
