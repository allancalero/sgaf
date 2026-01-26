<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$data = DB::table('clasificaciones')->select('id', 'nombre', 'prefijo')->get();
foreach($data as $d) {
    echo $d->id . " | " . $d->prefijo . " | " . $d->nombre . "\n";
}
