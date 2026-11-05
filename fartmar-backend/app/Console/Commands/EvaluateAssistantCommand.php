<?php

namespace App\Console\Commands;

use App\Contracts\AIProviderInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EvaluateAssistantCommand extends Command
{
    protected $signature = 'assistant:evaluate';
    protected $description = 'Ejecuta el conjunto de evaluación de 10 preguntas del asistente de IA';

    public function handle(AIProviderInterface $aiProvider): int
    {
        $path = base_path('tests/Fixtures/assistant_eval_dataset.json');
        if (!File::exists($path)) {
            $this->error("No se encontró el dataset de evaluación en {$path}");
            return self::FAILURE;
        }

        $dataset = json_decode(File::get($path), true);
        $aciertos = 0;
        $total = count($dataset);

        $this->info("Iniciando evaluación del asistente de IA ({$total} casos)...\n");

        foreach ($dataset as $test) {
           $resultado = $aiProvider->chat($test['pregunta'], \App\Services\InventoryAssistantTools::getToolDefinitions());
            $toolDetectada = $resultado['tool'] ?? null;
            $esperada = $test['herramienta_esperada'] ?? null;

            $exito = ($toolDetectada === $esperada);
            if ($exito) {
                $aciertos++;
                $this->line("✅ [Caso #{$test['id']}] OK - Pregunta: '{$test['pregunta']}'");
            } else {
                $this->warn("❌ [Caso #{$test['id']}] FALLO - Pregunta: '{$test['pregunta']}' (Esperado: " . ($esperada ?? 'Texto') . ", Detectado: " . ($toolDetectada ?? 'Texto') . ")");
            }
        }

        $porcentaje = ($aciertos / $total) * 100;
        $this->info("\n--- Resultados de la Evaluación ---");
        $this->info("Aciertos: {$aciertos} / {$total} ({$porcentaje}%)");

        return self::SUCCESS;
    }
}