<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAdquisicion extends Model
{
    protected $table = 'tipos_adquisicion';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado'
    ];
}
