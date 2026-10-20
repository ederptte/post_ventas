<?php

namespace App\Http\Requests;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateVentaRequest extends StoreVentaRequest
{
    public function rules(): array
    {
        // Al editar solo se reemplazan los productos; cliente y tipo de pago no cambian
        $rules = Arr::except(parent::rules(), ['cliente_id', 'tipo_pago']);

        // Un producto que ya estaba en la venta pudo desactivarse después,
        // así que aquí solo se exige que exista (no que esté activo)
        $rules['productos.*.producto_id'] = [
            'required',
            'integer',
            'distinct',
            Rule::exists('productos', 'id'),
        ];

        return $rules;
    }
}