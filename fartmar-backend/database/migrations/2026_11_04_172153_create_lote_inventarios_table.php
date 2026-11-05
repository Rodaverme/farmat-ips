<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bodega_id')->constrained('bodegas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('lote');
            $table->date('fecha_vencimiento');
            $table->integer('cantidad')->default(0);
            $table->integer('stock_minimo')->default(10);
            $table->timestamps();

            // Agregar una restricción de unicidad para evitar duplicados de lote por bodega y producto
            $table->unique(['bodega_id', 'producto_id', 'lote']);
        });

        // Agregar una restricción de verificación para asegurar que la cantidad sea mayor o igual a cero
        DB::statement('ALTER TABLE lote_inventarios ADD CONSTRAINT chk_cantidad_positiva CHECK (cantidad >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_inventarios');
    }
};
