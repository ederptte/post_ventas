<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'numero_pedido'                     => 'nullable|string|max:50',
            'proveedor_id'                      => 'required|integer|exists:proveedores,id',
            'tipo_pago'                         => 'required|in:contado,credito',
            'productos'                         => 'required|array|min:1',
            'productos.*.producto_id'           => 'required|integer|exists:productos,id',
            'productos.*.cantidad'              => 'required|integer|min:1',
            'productos.*.precio_compra_pactado' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'Debe seleccionar un proveedor para realizar el pedido.',
            'proveedor_id.exists'   => 'El proveedor seleccionado no es válido.',
            'productos.required'    => 'Debe añadir al menos un producto al pedido.',
            'productos.array'       => 'El formato de los productos no es correcto.',
            'productos.min'         => 'El pedido debe contener como mínimo 1 producto.',
            'tipo_pago.required'    => 'Debe especificar si el tipo de pago es "contado" o "credito".',
            'tipo_pago.in'          => 'El tipo de pago seleccionado no es válido. Debe ser "contado" o "credito".',


            'productos.*.producto_id.required'  => 'El ID del producto es obligatorio.',
            'productos.*.producto_id.exists'    => 'Uno de los productos seleccionados no existe en el sistema.',
            'productos.*.cantidad.required'     => 'La cantidad es obligatoria.',
            'productos.*.cantidad.min'          => 'La cantidad mínima por producto debe ser de 1 unidad.',
            'productos.*.precio_compra_pactado.required' => 'El precio de compra es obligatorio.',
            'productos.*.precio_compra_pactado.min'      => 'El precio de compra no puede ser menor a 0.',
        ];
    }
}
