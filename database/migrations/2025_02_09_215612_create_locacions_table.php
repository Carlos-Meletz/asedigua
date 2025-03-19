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
        Schema::create('locacions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'Departamento' o 'Municipio'
            $table->string('name'); // Nombre del departamento o municipio
            $table->unsignedBigInteger('parent_id')->nullable(); // ID del departamento para los municipios
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locacions');
    }
};
