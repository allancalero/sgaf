<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUbicacionRequest extends FormRequest
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
                Rule::unique('ubicaciones', 'nombre')->ignore($this->route('ubicacion')),
            ],
            'direccion' => ['nullable', 'string', 'max:500'],
            'estado' => ['required', 'string', 'max:20'],
        ];
    }
}
