<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuenteFinanciamiento extends Model
{
    use HasFactory;

    protected $table = 'fuentes_financiamiento';

    protected $fillable = [
        'nombre',
        'estado',
    ];
}
