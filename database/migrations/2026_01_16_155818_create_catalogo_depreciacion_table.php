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
        Schema::create('catalogo_depreciacion', function (Blueprint $table) {
            $table->id();
            $table->string('categoria_general');
            $table->string('especifica')->nullable();
            $table->string('mas_especifica')->nullable();
            $table->integer('vida_util_anos');
            $table->decimal('tasa_anual', 5, 2);
            $table->decimal('tasa_mensual', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_depreciacion');
    }
};
