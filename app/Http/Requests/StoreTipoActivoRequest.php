<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTipoActivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255', 'unique:tipos_activos,nombre'],
            'clasificacion_id' => ['required', 'integer', 'exists:clasificaciones,id'],
        ];
    }
}
