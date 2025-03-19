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
        Schema::create('crelineas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nombre de la línea de crédito
            $table->decimal('tasa_interes', 5, 2); // Tasa de interés
            $table->decimal('tasa_mora', 5, 2); // Tasa de interés moratorios
            $table->integer('plazo_min'); // Plazo mínimo en meses
            $table->integer('plazo_max'); // Plazo máximo en meses
            $table->decimal('monto_min', 10, 2); // Monto mínimo del préstamo
            $table->decimal('monto_max', 10, 2); // Monto máximo del préstamo
            $table->boolean('activo')->default(false); //Estado de la linea
            $table->text('condiciones')->nullable(); // Condiciones adicionales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crelineas');
    }
};
