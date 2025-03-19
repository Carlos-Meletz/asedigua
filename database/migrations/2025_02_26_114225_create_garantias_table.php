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
        Schema::create('garantias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credito_id')->constrained('creditos')->onDelete('cascade');
            $table->string('tipo_garantia');
            $table->string('Descriptor')->nullable();
            $table->decimal('valor_estimado', 12, 2);
            $table->text('descripcion');
            $table->text('observaciones')->nullable();

            //  Si es Hipoteca o Prendaria
            $table->string('numero_documento')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('ubicacion')->nullable();
            $table->decimal('superficie', 10, 2)->nullable();
            $table->string('registro_propiedad')->nullable();
            $table->string('nombre_propietario')->nullable();
            $table->json('documentos')->nullable();
            $table->string('notario_responsable')->nullable();
            $table->date('fecha_registro')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantias');
    }
};
