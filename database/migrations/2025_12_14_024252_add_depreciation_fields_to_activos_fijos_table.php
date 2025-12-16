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
        Schema::table('activos_fijos', function (Blueprint $table) {
            $table->integer('vida_util_anos')->nullable()->after('estado')->comment('Vida útil en años');
            $table->decimal('valor_residual', 12, 2)->nullable()->default(0)->after('vida_util_anos')->comment('Valor al final de vida útil');
            $table->string('metodo_depreciacion', 50)->default('LINEAL')->after('valor_residual')->comment('Método: LINEAL, SALDO_DECRECIENTE');
            $table->decimal('depreciacion_anual', 12, 2)->nullable()->after('metodo_depreciacion')->comment('Depreciación calculada por año');
            $table->decimal('depreciacion_acumulada', 12, 2)->nullable()->default(0)->after('depreciacion_anual')->comment('Total depreciado hasta la fecha');
            $table->decimal('valor_libros', 12, 2)->nullable()->after('depreciacion_acumulada')->comment('Valor actual del activo');
            $table->date('fecha_ultima_depreciacion')->nullable()->after('valor_libros')->comment('Última vez que se calculó');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activos_fijos', function (Blueprint $table) {
            $table->dropColumn([
                'vida_util_anos',
                'valor_residual',
                'metodo_depreciacion',
                'depreciacion_anual',
                'depreciacion_acumulada',
                'valor_libros',
                'fecha_ultima_depreciacion',
            ]);
        });
    }
};
