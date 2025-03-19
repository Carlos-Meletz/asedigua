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
        Schema::create('viviendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->string('tipo');
            $table->string('direccion');
            $table->integer('tiempo_residencia');
            $table->string('condiciones_vivienda');
            $table->boolean('servicio_agua')->default(false);
            $table->boolean('servicio_energia')->default(false);
            $table->boolean('servicio_alcantarillado')->default(false);
            $table->boolean('servicio_internet')->default(false);
            $table->boolean('servicio_telefono')->default(false);
            $table->decimal('valor_estimado', 12, 2)->nullable();
            $table->decimal('monto_alquiler', 10, 2)->nullable();
            $table->string('nombre_propietario')->nullable();
            $table->text('referencia_ubicacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viviendas');
    }
};
