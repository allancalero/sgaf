<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChequeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_cheque' => ['required', 'string', 'max:50', 'unique:cheques,numero_cheque'],
            'banco' => ['required', 'string', 'max:100'],
            'cuenta_bancaria' => ['required', 'string', 'max:50'],
            'monto_total' => ['required', 'numeric', 'min:0'],
            'saldo_disponible' => ['sometimes', 'numeric', 'min:0'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'beneficiario' => ['required', 'string', 'max:255'],
            'beneficiario_ruc' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', 'in:EMITIDO,COBRADO,ANULADO'],
            'area_solicitante_id' => ['required', 'exists:areas,id'],
            'usuario_emisor_id' => ['sometimes', 'exists:users,id'],
        ];
    }

    protected function prepareForValidation()
    {
        // Inicializar saldo_disponible con monto_total si no se proporciona
        if (!$this->has('saldo_disponible')) {
            $this->merge([
                'saldo_disponible' => $this->monto_total,
            ]);
        }

        // Asignar usuario emisor automáticamente
        $this->merge([
            'usuario_emisor_id' => auth()->id(),
        ]);
    }
}
