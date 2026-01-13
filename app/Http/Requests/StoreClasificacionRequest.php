<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClasificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prefijo' => ['required', 'string', 'max:10', 'unique:clasificaciones,prefijo'],
            'codigo' => ['nullable', 'string', 'max:50', 'unique:clasificaciones,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
        ];
    }
}
