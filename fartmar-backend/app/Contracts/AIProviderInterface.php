<?php

namespace App\Contracts;

interface AIProviderInterface
{
    public function chat(string $prompt, array $tools = [], array $context = []): array;

}