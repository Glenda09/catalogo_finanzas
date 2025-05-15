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
        Schema::create('progresos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_modulo')->constrained('modulos')->onDelete('cascade');
            $table->foreignId('id_inscripcion')->constrained('inscripcions')->onDelete('cascade');
            $table->date('fecha_inicio');
            $table->boolean('completado')->default(false);
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->date('fecha_fin')->nullable();
            $table->integer('tiempo_total')->nullable(); // Tiempo total en minutos
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progresos');
    }
};
