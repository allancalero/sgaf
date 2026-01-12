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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('personal_id')->nullable()->after('email');
            // We can add a foreign key constraint if we want, but simple column is enough for now to avoid constraint errors on existing data if we are not careful.
            // But let's be clean.
            // $table->foreign('personal_id')->references('id')->on('personal'); 
            // I'll skip foreign key constraint for safety on existing data without cleaning it up first, just index it.
            $table->index('personal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('personal_id');
        });
    }
};
