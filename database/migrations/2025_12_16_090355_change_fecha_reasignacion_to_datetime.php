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
            $table->dateTime('fecha_reasignacion')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reasignaciones', function (Blueprint $table) {
            $table->date('fecha_reasignacion')->change();
        });
    }
};
