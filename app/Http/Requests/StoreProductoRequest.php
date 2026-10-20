<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
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
            'codigo_barra'      => 'required|string|unique:productos,codigo_barra',
            'nombre_producto'   => 'required|string|max:255',
            'presentacion'      => 'required|string|max:100', // Sincronizado con el límite de la migración
            'unidad_medida'     => 'required|in:pieza,kilogramo', // Limpiado el conflicto sometimes|required
            'precio_costo'      => 'required|numeric|min:0',
            'precio_venta'      => 'required|numeric|min:0|gte:precio_costo',
            'alerta_bajo_stock' => 'nullable|numeric|min:0',
            'stock'             => 'nullable|numeric|min:0',
            'activo'            => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_barra.required'     => 'El código de barras es obligatorio.',
            'codigo_barra.unique'       => 'Este código de barras ya está registrado en el sistema.',
            'nombre_producto.required'  => 'El nombre del producto es obligatorio.',
            'presentacion.required'     => 'La descripción de la presentación es obligatoria.',
            'presentacion.max'          => 'La presentación no puede tener más de 100 caracteres.',
            'unidad_medida.required'    => 'Debe seleccionar una unidad de medida.',
            'unidad_medida.in'          => 'La unidad de medida debe ser "pieza" o "kilogramo".',
            'precio_costo.required'     => 'El precio de costo es obligatorio.',
            'precio_costo.numeric'      => 'El precio de costo debe ser un número válido.',
            'precio_costo.min'          => 'El precio de costo no puede ser menor a 0.',
            'precio_venta.required'     => 'El precio de venta es obligatorio.',
            'precio_venta.numeric'      => 'El precio de venta debe ser un número válido.',
            'precio_venta.min'          => 'El precio de venta no puede ser menor a 0.',
            'precio_venta.gte'          => 'El precio de venta no puede ser menor al precio de costo.',
            'stock.numeric'             => 'El stock inicial debe ser un número válido.',
            'stock.min'                 => 'El stock inicial no puede ser negativo.',
            'alerta_bajo_stock.numeric' => 'La alerta de stock bajo debe ser un número válido.',
            'alerta_bajo_stock.min'     => 'La alerta de stock bajo no puede ser negativa.',
            'stock.numeric'             => 'El stock inicial debe ser un número válido.',
            'stock.min'                 => 'El stock inicial no puede ser negativo.',
            'activo.boolean'            => 'El valor de activo debe ser verdadero o falso.'
        ];
    }
}