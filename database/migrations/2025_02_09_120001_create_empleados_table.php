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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agencia_id')->constrained()->onDelete('cascade');  // Relación con la tabla personas
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');  // Relación con la tabla personas
            $table->string('cargo');
            $table->decimal('salario', 10, 2);
            $table->date('fecha_ingreso');
            $table->date('fecha_salida')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
