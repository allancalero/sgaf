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
<<<<<<< HEAD

    // Relationship with custom fields
    public function customFields()
    {
        return $this->hasMany(ClassificationField::class, 'clasificacion_id')->orderBy('order');
    }
=======
>>>>>>> 8f3e0761afe5c74474f514ac2afef3e6d88db82c
}
