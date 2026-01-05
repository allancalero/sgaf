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

    // Relationships
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    // Inverse relationship
    public function activosFijos()
    {
        return $this->hasMany(ActivoFijo::class, 'personal_id');
    }
}
