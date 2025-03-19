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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
             //datos personales
             $table->string('nombre',50);
             $table->string('apellido',50);
             $table->string('nombre_completo')->virtualAs("CONCAT(nombre, ' ', apellido)");
             $table->date('fecha_nacimiento');
             $table->integer('edad');
             $table->enum('genero',['masculino','femenino']);
             $table->string('dpi',20)->unique();
             $table->string('dpi_dep',30);
             $table->string('dpi_mun',30);
             $table->string('estado_civil');
             $table->enum('estado',['activo','inactivo','suspendido']);
             $table->string('fotografia');
             //contacto
             $table->string('telefono',20);
             $table->string('celular',20)->nullable();
             $table->string('correo',50)->nullable();
             $table->boolean('social');
             $table->json('archivos');
             //direccion
             $table->string('departamento');
             $table->string('municipio');
             $table->string('direccion');
             $table->decimal('latitude', 10, 8); // Latitud con precisión decimal
             $table->decimal('longitude', 11, 8); // Longitud con precisión decimal
             $table->string('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
