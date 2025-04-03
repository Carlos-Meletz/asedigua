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
        Schema::create('crmovimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agencia_id')->constrained('agencias')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('caja_id')->constrained('cajas')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('credito_id')->constrained('creditos')->onUpdate('cascade')->onDelete('restrict');
            $table->dateTime('fecha');
            $table->string('comprobante');
            $table->enum('tipo', ['efectivo', 'banco']);
            //ingresos
            $table->decimal('capital', 15, 2)->default(0);
            $table->decimal('interes', 15, 2)->default(0);
            $table->decimal('descint', 15, 2)->default(0);
            $table->decimal('mora', 15, 2)->default(0);
            $table->decimal('descmora', 15, 2)->default(0);
            $table->decimal('otros', 15, 2)->default(0);
            $table->decimal('saldocap', 15, 2)->default(0);
            $table->decimal('saldoint', 15, 2)->default(0);
            $table->decimal('saldomor', 15, 2)->default(0);
            //egresos
            $table->decimal('desembolso', 15, 2)->default(0);
            $table->decimal('descuentos', 15, 2)->default(0);
            //
            $table->decimal('ingreso', 15, 2)->default(0);
            $table->decimal('egreso', 15, 2)->default(0);

            $table->integer('atraso')->default(0);
            $table->text('notas')->nullable();
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
        Schema::dropIfExists('crmovimientos');
    }
};
