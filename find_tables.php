<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// List all tables
$tables = DB::select('SHOW TABLES');
$dbName = env('DB_DATABASE');
$key = 'Tables_in_' . $dbName;

echo "Tablas que contienen 'activ' o 'asset':\n";
foreach ($tables as $t) {
    $name = $t->$key;
    if (stripos($name, 'activ') !== false || stripos($name, 'asset') !== false) {
        echo "  - {$name}\n";
    }
}
