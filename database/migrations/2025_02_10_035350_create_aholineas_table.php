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
        Schema::create('aholineas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nombre de la línea de ahorro
            $table->decimal('tasa_interes', 5, 2); // Tasa de interés asociada
            $table->decimal('tasa_interes_minima', 5, 2); // Tasa de interés minima en caso de vencimiento
            $table->decimal('tasa_penalizacion', 5, 2); // Tasa de interés por penalizaciones
            $table->integer('plazo_minimo'); // Plazo mínimo
            $table->integer('plazo_maximo'); // Plazo máximo
            $table->decimal('monto_min', 10, 2); // Monto mínimo del ahorro
            $table->decimal('monto_max', 10, 2); // Monto máximo del ahorro
            $table->boolean('activo')->default(false); //Estado de la linea
            $table->text('condiciones')->nullable(); // Condiciones específicas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aholineas');
    }
};
