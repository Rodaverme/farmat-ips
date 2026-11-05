<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('traslados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bodega_origen_id')->constrained('bodegas')->cascadeOnDelete();
            $table->foreignId('bodega_destino_id')->constrained('bodegas')->cascadeOnDelete();
            $table->foreignId('solicitante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('aprobador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado', [
                'BORRADOR',
                'SOLICITADO',
                'APROBADO',
                'EN_TRANSITO',
                'RECIBIDO',
                'RECIBIDO_PARCIAL',
                'ANULADO',
            ])->default('SOLICITADO');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traslados');
    }
};
