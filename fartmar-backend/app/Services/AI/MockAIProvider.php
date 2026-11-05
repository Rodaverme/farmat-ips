<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;

class MockAIProvider implements AIProviderInterface
{
    public function chat(string $prompt, array $tools = [], array $context = []): array
    {
        $promptLower = strtolower($prompt);

        if (str_contains($promptLower, 'stock crítico') || (str_contains($promptLower, 'hospitalización') && str_contains($promptLower, 'stock'))) {
            return [
                'type' => 'tool_call',
                'tool' => 'consultar_stock_minimo',
                'arguments' => [],
            ];
        }

        if (str_contains($promptLower, 'stock mínimo') || str_contains($promptLower, 'por debajo')) {
            return [
                'type' => 'tool_call',
                'tool' => 'consultar_stock_minimo',
                'arguments' => [],
            ];
        }

        if (
            str_contains($promptLower, 'vencen') ||
            str_contains($promptLower, 'vencidos') ||
            str_contains($promptLower, 'vencimientos') ||
            str_contains($promptLower, 'morfina') ||
            str_contains($promptLower, 'ibuprofeno') ||
            str_contains($promptLower, 'acetaminofén')
        ) {
            $producto = null;
            if (str_contains($promptLower, 'acetaminofén')) {
                $producto = 'Acetaminofén';
            } elseif (str_contains($promptLower, 'morfina')) {
                $producto = 'Morfina';
            } elseif (str_contains($promptLower, 'ibuprofeno')) {
                $producto = 'Ibuprofeno';
            }

            $dias = 90;
            if (str_contains($promptLower, '30 días')) {
                $dias = 30;
            } elseif (str_contains($promptLower, '60 días')) {
                $dias = 60;
            } elseif (str_contains($promptLower, '90 días')) {
                $dias = 90;
            }

            $bodega = null;
            if (str_contains($promptLower, 'farmacia central')) {
                $bodega = 'Farmacia Central';
            } elseif (str_contains($promptLower, 'urgencias')) {
                $bodega = 'Farmacia Urgencias';
            }

            return [
                'type' => 'tool_call',
                'tool' => 'consultar_lotes_vencimiento',
                'arguments' => [
                    'producto' => $producto,
                    'dias' => $dias,
                    'bodega' => $bodega,
                ],
            ];
        }

        return [
            'type' => 'text',
            'content' => 'Lo siento, esa consulta está fuera de mi alcance o no dispongo de información suficiente para responderla de forma segura.',
        ];
    }
}
