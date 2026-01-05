<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuenteFinanciamientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255', 'unique:fuentes_financiamiento,nombre'],
            'estado' => ['required', 'string', 'max:20'],
        ];
    }
}
