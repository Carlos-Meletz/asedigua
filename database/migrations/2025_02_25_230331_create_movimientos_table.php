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
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agencia_id')->constrained('agencias')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('caja_id')->constrained('cajas')->onUpdate('cascade')->onDelete('restrict');
            $table->dateTime('fecha');
            $table->string('comprobante');
            $table->enum('tipo', ['efectivo', 'banco']);
            $table->text('descripcion')->nullable();
            $table->decimal('ingreso', 15, 2)->default(0);
            $table->decimal('egreso', 15, 2)->default(0);
            $table->string('creado_por');
            $table->string('actualizado_por')->nullable();
            $table->boolean('anulado')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
