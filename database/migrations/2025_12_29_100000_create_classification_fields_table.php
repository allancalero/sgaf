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
        Schema::create('classification_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clasificacion_id')->constrained('clasificaciones')->cascadeOnDelete();
            $table->string('field_name');     // motor, placa, chasis
            $table->string('field_label');    // Motor, Placa, Chasis
            $table->string('field_type')->default('text');  // text, number, select, date
            $table->json('field_options')->nullable(); // Para campos select
            $table->boolean('required')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            // Índice para búsquedas rápidas
            $table->index(['clasificacion_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classification_fields');
    }
};
