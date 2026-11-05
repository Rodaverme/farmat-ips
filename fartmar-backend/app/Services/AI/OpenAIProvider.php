<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use App\Services\InventoryAssistantTools;
use Illuminate\Support\Facades\Http;

class OpenAIProvider implements AIProviderInterface
{
    public function chat(string $prompt, array $tools = [], array $context = []): array
    {
        $apiKey = config('services.openai.key');

        // Si no se pasaron herramientas como argumento, cargamos las del inventario por defecto
        if (empty($tools)) {
            $tools = InventoryAssistantTools::getToolDefinitions();
        }

        $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Eres el asistente de inventario de FARTMAR IPS. Analiza la consulta del usuario y utiliza obligatoriamente las herramientas disponibles si la pregunta está relacionada con vencimientos de lotes o stock mínimo. Nunca reveles datos de pacientes.',
                ],
                ['role' => 'user', 'content' => $prompt],
            ],
            'tools' => $tools,
            'tool_choice' => 'auto',
        ]);

        $data = $response->json();
        $message = $data['choices'][0]['message'] ?? [];

        // Si OpenAI decide llamar a una herramienta
        if (isset($message['tool_calls']) && count($message['tool_calls']) > 0) {
            $toolCall = $message['tool_calls'][0];

            return [
                'type' => 'tool_call',
                'tool' => $toolCall['function']['name'],
                'arguments' => json_decode($toolCall['function']['arguments'], true) ?? [],
            ];
        }

        // Si responde con texto plano
        return [
            'type' => 'text',
            'content' => $message['content'] ?? 'No se pudo procesar la solicitud.',
        ];
    }
}
