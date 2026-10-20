<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaAbono extends Model
{
    protected $table = 'venta_abonos';

    protected $fillable = [
        'venta_id',
        'monto_abonado',
        'fecha_pago',
        'nota_referencia',
    ];

    protected function casts(): array
    {
        return [
            'monto_abonado' => 'decimal:2',
            'fecha_pago'    => 'date',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }
}