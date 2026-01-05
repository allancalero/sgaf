<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFuenteFinanciamientoRequest extends FormRequest
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
                Rule::unique('fuentes_financiamiento', 'nombre')->ignore($this->route('fuente')),
            ],
            'estado' => ['required', 'string', 'max:20'],
        ];
    }
}
