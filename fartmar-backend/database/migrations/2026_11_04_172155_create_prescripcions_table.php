<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('medico_id')->constrained('users');
            $table->foreignId('producto_id')->constrained('productos');
            $table->integer('cantidad_prescrita');
            $table->integer('cantidad_dispensada')->default(0);
            $table->date('fecha_prescripcion');
            $table->string('diagnostico')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescripciones');
    }
};
