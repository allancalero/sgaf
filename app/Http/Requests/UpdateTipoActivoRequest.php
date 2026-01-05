<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoActivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tipos_activos', 'nombre')->ignore($this->route('tipo')),
            ],
            'clasificacion_id' => ['required', 'integer', 'exists:clasificaciones,id'],
        ];
    }
}
