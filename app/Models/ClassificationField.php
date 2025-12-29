<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassificationField extends Model
{
    use HasFactory;

    protected $fillable = [
        'clasificacion_id',
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'required',
        'order'
    ];

    protected $casts = [
        'field_options' => 'array',
        'required' => 'boolean',
    ];

    public function clasificacion()
    {
        return $this->belongsTo(Clasificacion::class, 'clasificacion_id');
    }
}
