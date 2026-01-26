<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

if (Schema::hasColumn('activos_fijos', 'foto')) {
    echo "La columna 'foto' YA EXISTE en activos_fijos\n";
} else {
    echo "La columna 'foto' NO existe en activos_fijos\n";
}
