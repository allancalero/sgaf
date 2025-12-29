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
        // Remove area_id from ubicaciones
        Schema::table('ubicaciones', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });
        
        // Add ubicacion_id to areas
        Schema::table('areas', function (Blueprint $table) {
            $table->unsignedBigInteger('ubicacion_id')->nullable()->after('nombre');
            $table->foreign('ubicacion_id')->references('id')->on('ubicaciones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: add area_id back to ubicaciones
        Schema::table('ubicaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('area_id')->nullable()->after('direccion');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
        });
        
        // Remove ubicacion_id from areas
        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign(['ubicacion_id']);
            $table->dropColumn('ubicacion_id');
        });
    }
};
