<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_alcaldia',
        'alcaldesa',
        'gerente',
        'moneda',
        'ano_fiscal',
        'logo_url',
    ];
}
