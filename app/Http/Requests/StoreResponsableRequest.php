<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResponsableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'id_cargo' => ['required', 'integer', 'exists:cargos,id'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'estado' => ['required', 'string', 'max:20'],
        ];
    }
}
