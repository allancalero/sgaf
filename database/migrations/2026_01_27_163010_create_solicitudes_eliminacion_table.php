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
        Schema::create('solicitudes_eliminacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activo_id');
            $table->unsignedBigInteger('solicitante_id');
            $table->text('motivo');
            $table->enum('estado', ['PENDIENTE', 'APROBADO', 'RECHAZADO'])->default('PENDIENTE');
            $table->unsignedBigInteger('procesado_por')->nullable();
            $table->text('nota_admin')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('activo_id')->references('id')->on('activos_fijos')->onDelete('cascade');
            $table->foreign('solicitante_id')->references('id')->on('users');
            $table->foreign('procesado_por')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_eliminacion');
    }
};
