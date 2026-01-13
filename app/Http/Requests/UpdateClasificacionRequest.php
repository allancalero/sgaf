<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClasificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prefijo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('clasificaciones', 'prefijo')->ignore($this->route('clasificacion')),
            ],
            'codigo' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('clasificaciones', 'codigo')->ignore($this->route('clasificacion')),
            ],
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
