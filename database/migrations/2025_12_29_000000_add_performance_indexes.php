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
        // Add indexes to activos_fijos table
        Schema::table('activos_fijos', function (Blueprint $table) {
            $table->index('area_id', 'idx_activos_area');
            $table->index('ubicacion_id', 'idx_activos_ubicacion');
            $table->index('clasificacion_id', 'idx_activos_clasificacion');
            $table->index('personal_id', 'idx_activos_personal');
            $table->index('estado', 'idx_activos_estado');
            $table->index('fecha_adquisicion', 'idx_activos_fecha_adq');
            $table->index('tipo_activo_id', 'idx_activos_tipo');
            $table->index('fuente_financiamiento_id', 'idx_activos_fuente');
            $table->index('proveedor_id', 'idx_activos_proveedor');
        });

        // Add indexes to personal table
        Schema::table('personal', function (Blueprint $table) {
            $table->index('area_id', 'idx_personal_area');
            $table->index('cargo_id', 'idx_personal_cargo');
            $table->index('ubicacion_id', 'idx_personal_ubicacion');
            $table->index('estado', 'idx_personal_estado');
        });

        // Add composite index to historial_asignaciones table
        Schema::table('historial_asignaciones', function (Blueprint $table) {
            $table->index(['activo_id', 'fecha_asignacion'], 'idx_historial_activo_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activos_fijos', function (Blueprint $table) {
            $table->dropIndex('idx_activos_area');
            $table->dropIndex('idx_activos_ubicacion');
            $table->dropIndex('idx_activos_clasificacion');
            $table->dropIndex('idx_activos_personal');
            $table->dropIndex('idx_activos_estado');
            $table->dropIndex('idx_activos_fecha_adq');
            $table->dropIndex('idx_activos_tipo');
            $table->dropIndex('idx_activos_fuente');
            $table->dropIndex('idx_activos_proveedor');
        });

        Schema::table('personal', function (Blueprint $table) {
            $table->dropIndex('idx_personal_area');
            $table->dropIndex('idx_personal_cargo');
            $table->dropIndex('idx_personal_ubicacion');
            $table->dropIndex('idx_personal_estado');
        });

        Schema::table('historial_asignaciones', function (Blueprint $table) {
            $table->dropIndex('idx_historial_activo_fecha');
        });
    }
};
