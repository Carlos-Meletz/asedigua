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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('razon_social')->nullable();
            $table->string('nit')->unique();
            $table->string('tipo_empresa');
            $table->date('fecha_constitucion')->nullable();
            $table->string('direccion_fiscal');
            $table->string('rps_nombre');
            $table->string('rps_dpi', 20);
            $table->string('rps_dpiDep');
            $table->string('rps_dpiMun');
            $table->string('rps_cargo');
            $table->string('rps_profesion');
            $table->date('rps_fechaNac');
            $table->integer('rps_edad');
            $table->string('rps_estado_civil');
            $table->string('rps_direccion');
            $table->string('logo');
            $table->string('rps_telefono')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
