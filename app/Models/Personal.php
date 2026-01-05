<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';

    protected $fillable = [
        'nombre',
        'apellido',
        'cargo_id',
        'area_id',
        'ubicacion_id',
        'telefono',
        'email',
        'numero_empleado',
        'numero_cedula',
        'sexo',
        'fecha_nac',
        'edad',
        'direccion',
        'profesion',
        'estado',
        'foto',
    ];
}
