<?php

namespace App\Providers;

use App\Contracts\AIProviderInterface;
use App\Services\AI\MockAIProvider;
use App\Services\AI\OpenAIProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AIProviderInterface::class, function ($app) {
            // Usamos config() en lugar de env() para cumplir con Larastan
            $provider = config('services.ai.provider', 'mock');

            return $provider === 'openai'
                ? new OpenAIProvider
                : new MockAIProvider;
        });
    }

    public function boot(): void
    {
        //
    }
}
