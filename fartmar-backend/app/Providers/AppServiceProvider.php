<?php

namespace App\Providers;

use App\Contracts\AIProviderInterface;
use App\Services\AI\MockAIProvider;
use App\Services\AI\OpenAIProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIProviderInterface::class, function ($app) {
            return env('AI_PROVIDER', 'mock') === 'openai'
                ? new OpenAIProvider
                : new MockAIProvider;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
