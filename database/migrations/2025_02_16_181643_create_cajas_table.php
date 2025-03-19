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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agencia_id')->constrained('agencias')->onUpdate('cascade')->onDelete('restrict');
            $table->datetime('fecha_apertura');
            $table->datetime('fecha_cierre')->nullable();
            $table->decimal('ahingresos', 10, 2)->default(0);
            $table->decimal('ahegresos', 10, 2)->default(0);
            $table->decimal('cringresos', 10, 2)->default(0);
            $table->decimal('cregresos', 10, 2)->default(0);
            $table->decimal('otingresos', 10, 2)->default(0);
            $table->decimal('otegresos', 10, 2)->default(0);
            $table->decimal('totalingresos', 10, 2)->default(0);
            $table->decimal('totalegresos', 10, 2)->default(0);
            $table->decimal('saldo', 10, 2)->default(0);
            $table->string('creado_por');
            $table->string('actualizado_por')->nullable();
            $table->boolean('abierta')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
