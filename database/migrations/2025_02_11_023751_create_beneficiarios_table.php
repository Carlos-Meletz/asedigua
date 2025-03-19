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
        Schema::create('beneficiarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ahorro_id')->constrained()->onDelete('cascade');
            $table->string('nombre', 50);
            $table->string('apellido', 50);
            $table->string('nombre_completo')->virtualAs("CONCAT(nombre, ' ', apellido)");
            $table->string('dep_dpi');
            $table->string('mun_dpi');
            $table->string('dpi', 20);
            $table->string('relacion', 50);
            $table->text('direccion');
            $table->string('telefono', 15);
            $table->string('profesion', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiarios');
    }
};
