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
        Schema::create('reasignaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activo_id');
            $table->unsignedBigInteger('ubicacion_anterior_id')->nullable();
            $table->unsignedBigInteger('responsable_anterior_id')->nullable();
            $table->unsignedBigInteger('ubicacion_nueva_id')->nullable();
            $table->unsignedBigInteger('responsable_nuevo_id')->nullable();
            $table->text('motivo');
            $table->text('observaciones')->nullable();
            $table->date('fecha_reasignacion');
            $table->string('estado')->default('completada');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();
            
            $table->index('activo_id');
            $table->index('fecha_reasignacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reasignaciones');
    }
};
