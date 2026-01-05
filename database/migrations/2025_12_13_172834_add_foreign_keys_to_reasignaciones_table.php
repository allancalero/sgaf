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
        Schema::table('reasignaciones', function (Blueprint $table) {
            $table->foreign('activo_id')->references('id')->on('activos_fijos')->onDelete('cascade');
            $table->foreign('ubicacion_anterior_id')->references('id')->on('ubicaciones')->onDelete('set null');
            $table->foreign('responsable_anterior_id')->references('id')->on('personal')->onDelete('set null');
            $table->foreign('ubicacion_nueva_id')->references('id')->on('ubicaciones')->onDelete('set null');
            $table->foreign('responsable_nuevo_id')->references('id')->on('personal')->onDelete('set null');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('reasignaciones', function (Blueprint $table) {
            $table->dropForeign(['activo_id']);
            $table->dropForeign(['ubicacion_anterior_id']);
            $table->dropForeign(['responsable_anterior_id']);
            $table->dropForeign(['ubicacion_nueva_id']);
            $table->dropForeign(['responsable_nuevo_id']);
            $table->dropForeign(['usuario_id']);
        });
    }
};
