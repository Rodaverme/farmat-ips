<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traslado_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traslado_id')->constrained('traslados')->cascadeOnDelete();
            $table->foreignId('lote_inventario_id')->constrained('lote_inventarios')->cascadeOnDelete();
            $table->integer('cantidad_solicitada');
            $table->integer('cantidad_despachada')->default(0);
            $table->integer('cantidad_recibida')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traslado_detalles');
    }
};
