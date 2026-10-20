<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo_barra',
        'nombre_producto',
        'presentacion',
        'unidad_medida',
        'precio_costo',
        'precio_venta',
        'stock',
        'alerta_bajo_stock',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_costo'      => 'decimal:2',
            'precio_venta'      => 'decimal:2',
            'stock'             => 'decimal:2',
            'alerta_bajo_stock' => 'integer',
            'activo'            => 'boolean',
        ];
    }

    public function ventaDetalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeStockBajo(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'alerta_bajo_stock');
    }

    public function tieneStockBajo(): bool
    {
        return $this->stock <= $this->alerta_bajo_stock;
    }
}