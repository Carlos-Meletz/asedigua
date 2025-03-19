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
        Schema::create('crelineacostos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crelinea_id')->constrained('crelineas')->onDelete('cascade');
            $table->string('tipo'); // Tipo de costo: descuento, costo administrativo, etc.
            $table->boolean('es_porcentaje')->default(false); // Indica si es porcentaje o cantidad fija
            $table->decimal('valor', 10, 2); // Valor del costo
            $table->string('aplicacion')->default('desembolso');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crelineacostos');
    }
};
