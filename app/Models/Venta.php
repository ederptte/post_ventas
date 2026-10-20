<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'numero_venta',
        'cliente_id',
        'estado',
        'tipo_pago',
        'estado_pago',
        'total_venta',
        'monto_abonado',
        'saldo_pendiente',
        'fecha_entrega',
    ];

    protected function casts(): array
    {
        return [
            'total_venta'     => 'decimal:2',
            'monto_abonado'   => 'decimal:2',
            'saldo_pendiente' => 'decimal:2',
            'fecha_entrega'   => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function abonos(): HasMany
    {
        return $this->hasMany(VentaAbono::class);
    }
}