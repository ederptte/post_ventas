<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;

    // Si tu tabla se llama 'proveedores', Laravel la detecta por convención.
    protected $table = 'proveedores'; 

    protected $fillable = [
        'nombre_proveedor',
        'id_tributaria',
        'es_mayorista',
        'cuando_viene',
        'frecuencia_visita',
        'cuando_entrega',
        'nombre_vendedor',
        'telefono_vendedor',
        'activo'
    ];

    protected function casts(): array
    {
        return [
            'es_mayorista' => 'boolean',
            'cuando_viene' => 'array',
            'activo'       => 'boolean',
        ];
    }

    // Opcional para cuando crees el módulo de pedidos/compras
    /*
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
    */
}