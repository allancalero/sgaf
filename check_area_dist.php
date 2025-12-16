<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DISTRIBUCION DE PERSONAL POR AREA ===\n";
foreach(DB::table('personal')->select('area_id', DB::raw('count(*) as total'))->groupBy('area_id')->get() as $row) { 
    echo "Area ID " . $row->area_id . ": " . $row->total . " personas\n"; 
}
