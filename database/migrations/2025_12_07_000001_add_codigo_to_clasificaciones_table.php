<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clasificaciones', function (Blueprint $table) {
            $table->string('codigo', 30)->nullable()->after('id');
        });

        $driver = DB::getDriverName();

        if ($driver === 'sqlsrv') {
            // En SQL Server las columnas NULL no permiten múltiples NULL en índices únicos, se crea índice filtrado
            DB::statement(
                'CREATE UNIQUE INDEX clasificaciones_codigo_unique ON clasificaciones (codigo) WHERE codigo IS NOT NULL'
            );
        } else {
            Schema::table('clasificaciones', function (Blueprint $table) {
                $table->unique('codigo', 'clasificaciones_codigo_unique');
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlsrv') {
            DB::statement(
                "IF EXISTS (SELECT name FROM sys.indexes WHERE name = 'clasificaciones_codigo_unique' AND object_id = OBJECT_ID('clasificaciones')) DROP INDEX [clasificaciones_codigo_unique] ON [clasificaciones]"
            );
            Schema::table('clasificaciones', function (Blueprint $table) {
                $table->dropColumn('codigo');
            });
        } else {
            Schema::table('clasificaciones', function (Blueprint $table) {
                $table->dropUnique('clasificaciones_codigo_unique');
                $table->dropColumn('codigo');
            });
        }
    }
};
