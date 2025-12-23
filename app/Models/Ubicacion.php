<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    use HasFactory;

    // Explicit table name to avoid Laravel's default "ubicacions"
    protected $table = 'ubicaciones';

    protected $fillable = [
        'nombre',
        'direccion',
        'estado',
    ];
}
