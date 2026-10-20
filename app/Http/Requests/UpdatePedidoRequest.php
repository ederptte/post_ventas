<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proveedor_id'                      => 'sometimes|required|integer|exists:proveedores,id',
            'tipo_pago'                         => 'sometimes|required|in:contado,credito',
            'productos'                         => 'required|array|min:1',
            'productos.*.producto_id'           => 'required|integer|exists:productos,id',
            'productos.*.cantidad'              => 'required|integer|min:1',
            'productos.*.precio_compra_pactado' => 'required|numeric|min:0',
        ];
    }
}
