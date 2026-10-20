<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoAbono extends Model
{
    protected $table = 'pedido_abonos';
    protected $fillable = ['pedido_id', 'monto_abonado', 'fecha_pago', 'nota_referencia'];

    public function pedido() {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
