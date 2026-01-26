<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$data = DB::table('clasificaciones')
    ->select('id', 'prefijo', 'codigo', 'nombre')
    ->orderBy('codigo')
    ->get();

foreach($data as $d) {
    echo $d->id . " | " . ($d->prefijo ?? 'N/A') . " | " . ($d->codigo ?? 'N/A') . " | " . $d->nombre . "\n";
}
