<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 8, 2);
            $table->integer('duracion_horas');
            $table->foreignId('id_categoria')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('id_instructor')->constrained('instructores')->onDelete('cascade');
            $table->boolean('activo')->default(true);
            $table->foreignId('creado_por')->constrained('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
