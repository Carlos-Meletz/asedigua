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
        Schema::create('ahorros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agencia_id')->constrained('agencias')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('cliente_id')->constrained('clientes')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('fondo_id')->constrained('fondos')->onDelete('cascade');
            $table->foreignId('aholinea_id')->constrained('aholineas')->onDelete('cascade');
            $table->string('numero_cuenta')->unique();
            $table->enum('tipo', ['corriente', 'plazo_fijo']);
            $table->decimal('saldo', 15, 2)->default(0);
            $table->decimal('saldo_contrato', 15, 2)->default(0);
            $table->decimal('interes_acumulado', 15, 2)->default(0);
            $table->decimal('interes_anual', 5, 2)->default(0);
            $table->enum('estado', ['activa', 'inactiva', 'bloqueada']);
            $table->date('fecha_apertura');
            $table->integer('plazo');
            $table->date('fecha_vencimiento')->nullable();
            $table->boolean('nuevo')->default(true);
            $table->integer('numero_renovaciones')->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ahorros');
    }
};
