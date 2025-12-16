<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivoFijoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_inventario' => ['required', 'string', 'max:50', 'unique:activos_fijos,codigo_inventario'],
            'nombre_activo' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
            'serie' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'descripcion' => ['nullable', 'string'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'precio_unitario' => ['nullable', 'numeric', 'min:0'],
            'precio_adquisicion' => ['nullable', 'numeric', 'min:0'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'numero_factura' => ['nullable', 'string', 'max:100'],
            'cheque_id' => ['nullable', 'integer', 'exists:cheques,id'],
            'monto_cheque_utilizado' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', 'string', 'max:20'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'ubicacion_id' => ['required', 'integer', 'exists:ubicaciones,id'],
            'clasificacion_id' => ['required', 'integer', 'exists:clasificaciones,id'],
            'tipo_activo_id' => ['nullable', 'integer', 'exists:tipos_activos,id'],
            'fuente_financiamiento_id' => ['required', 'integer', 'exists:fuentes_financiamiento,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'personal_id' => ['nullable', 'integer', 'exists:personal,id'],
        ];
    }
}
