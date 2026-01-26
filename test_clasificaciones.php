<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$clasificaciones = DB::table('clasificaciones')
    ->orderBy('nombre')
    ->get();

echo "Total clasificaciones: " . count($clasificaciones) . "\n";
echo json_encode($clasificaciones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
