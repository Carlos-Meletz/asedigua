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
        Schema::create('agencias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nombre único de la agencia
            $table->string('codigo')->unique(); // Código único (opcional)
            $table->string('departamento');
            $table->string('municipio');
            $table->string('direccion');
            $table->decimal('latitude', 10, 8)->nullable(); // Latitud con precisión decimal
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('telefono');
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencias');
    }
};
