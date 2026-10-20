<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Venta
            'cliente_id' => [
                'required',
                'integer',
                Rule::exists('clientes', 'id')->where('activo', true),
            ],
            'tipo_pago' => ['required', 'in:contado,credito'],

            // Detalles
            'productos'                      => ['required', 'array', 'min:1'],
            'productos.*.producto_id'        => [
                'required',
                'integer',
                'distinct',
                Rule::exists('productos', 'id')->where('activo', true),
            ],
            'productos.*.cantidad'           => ['required', 'integer', 'min:1'],
            'productos.*.precio_venta_final' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            // Venta
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.integer'  => 'El cliente seleccionado no es válido.',
            'cliente_id.exists'   => 'El cliente seleccionado no existe o está inactivo.',
            'tipo_pago.required'  => 'El tipo de pago es obligatorio.',
            'tipo_pago.in'        => 'El tipo de pago seleccionado no es válido.',

            // Productos
            'productos.required' => 'Debe añadir al menos un producto a la venta.',
            'productos.array'    => 'El formato de los productos no es correcto.',
            'productos.min'      => 'La venta debe contener como mínimo 1 producto.',

            'productos.*.producto_id.required' => 'El ID del producto es obligatorio.',
            'productos.*.producto_id.integer'  => 'El ID del producto no es válido.',
            'productos.*.producto_id.distinct' => 'No puede repetir el mismo producto en la venta.',
            'productos.*.producto_id.exists'   => 'Uno de los productos seleccionados no existe o está inactivo.',

            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer'  => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min'      => 'La cantidad mínima por producto debe ser de 1 unidad.',

            'productos.*.precio_venta_final.required' => 'El precio de venta es obligatorio.',
            'productos.*.precio_venta_final.numeric'  => 'El precio de venta debe ser un número.',
            'productos.*.precio_venta_final.min'      => 'El precio de venta no puede ser menor a 0.',
            'productos.*.precio_venta_final.max'      => 'El precio de venta supera el valor máximo permitido.',
        ];
    }
}