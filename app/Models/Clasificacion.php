<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clasificacion extends Model
{
    use HasFactory;

    protected $table = 'clasificaciones';

    protected $fillable = [
        'codigo',
        'nombre',
    ];

    // Relationship with custom fields
    public function customFields()
    {
        return $this->hasMany(ClassificationField::class, 'clasificacion_id')->orderBy('order');
    }
}
