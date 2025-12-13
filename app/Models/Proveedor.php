<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    // Fix pluralization for SQL Server table
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'ruc',
        'direccion',
        'telefono',
        'email',
    ];
}
