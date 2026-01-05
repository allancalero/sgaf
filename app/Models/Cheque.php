<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cheque extends Model
{
    use HasFactory;

    protected $table = 'cheques';

    protected $fillable = [
        'numero_cheque',
        'banco',
        'cuenta_bancaria',
        'monto_total',
        'saldo_disponible',
        'fecha_emision',
        'fecha_vencimiento',
        'beneficiario',
        'beneficiario_ruc',
        'descripcion',
        'estado',
        'area_solicitante_id',
        'usuario_emisor_id',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'saldo_disponible' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function areaSolicitante(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_solicitante_id');
    }

    public function usuarioEmisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_emisor_id');
    }

    public function activosFijos(): HasMany
    {
        return $this->hasMany(ActivoFijo::class, 'cheque_id');
    }
}
