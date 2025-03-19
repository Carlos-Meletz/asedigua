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
        Schema::create('fiadors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credito_id')->constrained()->onDelete('cascade');
            $table->string('tipo', 20);
            $table->string('nombre', 50);
            $table->string('apellido', 50);
            $table->string('nombre_completo')->virtualAs("CONCAT(nombre, ' ', apellido)");
            $table->date('fecha_nacimiento');
            $table->integer('edad');
            $table->string('estado_civil');
            $table->string('dep_dpi');
            $table->string('mun_dpi');
            $table->string('dpi', 20);
            $table->string('relacion', 50);
            $table->string('profesion', 50);
            $table->string('direccion', 50);
            $table->string('telefono', 15);
            $table->boolean('firma')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiadors');
    }
};
