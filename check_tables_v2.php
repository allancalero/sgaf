<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = ['reasignaciones', 'historial_asignaciones'];
foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "Table: $table\n";
        $columns = Schema::getColumnListing($table);
        foreach ($columns as $column) {
            echo " - $column\n";
        }
    } else {
        echo "Table: $table does not exist\n";
    }
}
