<?php

namespace App\Http\Requests;

use App\Models\Producto;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoRequest extends FormRequest
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
        // Manejo seguro por si la ruta usa Route Model Binding o solo el ID
        $productoRoute = $this->route('producto');
        $productoId = $productoRoute instanceof Producto ? $productoRoute->id : $productoRoute;

        return [
            'codigo_barra'      => 'sometimes|required|string|unique:productos,codigo_barra,' . $productoId,
            'nombre_producto'   => 'sometimes|required|string|max:255',
            'presentacion'      => 'sometimes|required|string|max:100', // Sincronizado con la BD
            'unidad_medida'     => 'sometimes|required|in:pieza,kilogramo',
            'precio_costo'      => 'sometimes|required|numeric|min:0',
            'precio_venta'      => 'sometimes|required|numeric|min:0',
            'alerta_bajo_stock' => 'nullable|numeric|min:0', // Sincronizado con decimales si aplica
            'activo'            => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_barra.required'     => 'El código de barras es obligatorio.',
            'codigo_barra.unique'       => 'Este código de barras ya está registrado en otro producto.',
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
            'alerta_bajo_stock.numeric' => 'La alerta de stock bajo debe ser un número válido.',
        ];
    }
}