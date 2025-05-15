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
        Schema::create('usuario_sesions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('usuarios')->onDelete('cascade');
            $table->string('token')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_sesions');
    }
};
