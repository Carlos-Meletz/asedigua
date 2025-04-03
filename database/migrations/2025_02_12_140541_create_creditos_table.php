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
        Schema::create('creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agencia_id')->constrained('agencias')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('cliente_id')->constrained('clientes')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('fondo_id')->constrained('fondos')->onDelete('cascade');
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->foreignId('crelinea_id')->constrained('crelineas')->onDelete('cascade');
            $table->foreignId('destino_id')->constrained('destinos')->onDelete('cascade');
            $table->string('codigo');
            $table->decimal('monto_solicitado', 15, 2);
            $table->decimal('monto_aprobado', 15, 2)->default(0);
            $table->decimal('monto_desembolsado', 15, 2)->default(0);
            $table->decimal('descuentos', 15, 2);
            $table->decimal('saldo_capital', 15, 2)->default(0);
            $table->decimal('saldo_interes', 15, 2)->default(0);
            $table->decimal('saldo_mora', 15, 2)->default(0);
            $table->decimal('interes_anual', 5, 2);
            $table->integer('plazo');
            $table->string('tipo_cuota', 20);
            $table->enum('estado', ['solicitado', 'desembolsado', 'rechazado', 'vencidomora', 'atrasado', 'pagado', 'vencido'])->default('solicitado');
            $table->date('fecha_desembolso')->nullable();
            $table->date('fecha_primerpago')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('dias_atraso')->default(0);
            $table->decimal('cuota', 15, 2);
            $table->date('fecha_ultimopago')->nullable();
            $table->integer('numero_renovaciones')->default(0);
            $table->text('notas')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creditos');
    }
};
