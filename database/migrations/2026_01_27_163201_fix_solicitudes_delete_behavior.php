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
        Schema::table('solicitudes_eliminacion', function (Blueprint $table) {
            // Drop existing FK
            $table->dropForeign(['activo_id']);
            
            // Make nullable
            $table->unsignedBigInteger('activo_id')->nullable()->change();
            
            // Re-add FK with Set Null
            $table->foreign('activo_id')
                  ->references('id')->on('activos_fijos')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way fix usually, but for completeness:
        Schema::table('solicitudes_eliminacion', function (Blueprint $table) {
            $table->dropForeign(['activo_id']);
            $table->unsignedBigInteger('activo_id')->nullable(false)->change();
            $table->foreign('activo_id')->references('id')->on('activos_fijos')->onDelete('cascade');
        });
    }
};
