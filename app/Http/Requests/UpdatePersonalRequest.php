<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'cargo_id' => ['required', 'integer', 'exists:cargos,id'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'ubicacion_id' => ['required', 'integer', 'exists:ubicaciones,id'],
            'telefono' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'numero_empleado' => ['nullable', 'string', 'max:50'],
            'numero_cedula' => ['nullable', 'string', 'max:50'],
            'fecha_nac' => ['nullable', 'date'],
            'edad' => ['nullable', 'integer', 'min:0'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'profesion' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:20'],
            'foto' => ['nullable', 'string', 'max:255'],
        ];
    }
}
