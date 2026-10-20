<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'numero_pedido',
        'proveedor_id',
        'estado',
        'tipo_pago',
        'estado_pago',
        'total_pedido',
        'monto_abonado',
        'saldo_pendiente',
        'fecha_recibido'
    ];

    protected function casts(): array
    {
        return [
            'total_pedido'    => 'decimal:2',
            'monto_abonado'   => 'decimal:2',
            'saldo_pendiente' => 'decimal:2',
            'fecha_recibido'  => 'datetime',
        ];
    }

    public function proveedor() {
        return $this->belongsTo(Proveedor::class, 'proveedor_id'); 
    }

    public function detalles() {
        return $this->hasMany(PedidoDetalle::class, 'pedido_id'); 
    }
        
    public function abonos() {
        return $this->hasMany(PedidoAbono::class, 'pedido_id');
    }
}