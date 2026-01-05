<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChequeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $chequeId = $this->route('cheque')->id;

        return [
            'numero_cheque' => ['required', 'string', 'max:50', 'unique:cheques,numero_cheque,' . $chequeId],
            'banco' => ['required', 'string', 'max:100'],
            'cuenta_bancaria' => ['required', 'string', 'max:50'],
            'monto_total' => ['required', 'numeric', 'min:0'],
            'saldo_disponible' => ['required', 'numeric', 'min:0', 'lte:monto_total'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'beneficiario' => ['required', 'string', 'max:255'],
            'beneficiario_ruc' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', 'in:EMITIDO,COBRADO,ANULADO'],
            'area_solicitante_id' => ['required', 'exists:areas,id'],
        ];
    }
}
