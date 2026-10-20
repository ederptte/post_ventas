<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProveedorRequest extends FormRequest
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
            'nombre_proveedor'  => 'required|string|max:255',
            'id_tributaria'     => 'required|string|max:30|unique:proveedores,id_tributaria', // Cambiado a string para admitir guiones o formatos alfanuméricos
            'es_mayorista'      => 'required|boolean',
            'frecuencia_visita' => ['required', Rule::in(['semanal', 'quincenal', 'mensual'])],
            'cuando_entrega'    => ['required', Rule::in(['mismo_dia', 'dia_siguiente', 'otro_dia'])],
            'nombre_vendedor'   => 'nullable|string|max:255',
            'telefono_vendedor' => 'nullable|string|max:30', // Alineado con la longitud de la migración
            'cuando_viene'      => ['required', 'array', 'min:1'], // Aseguramos que al menos traiga un elemento
            'cuando_viene.*'    => ['distinct', Rule::in(['lun', 'mar', 'mie', 'jue', 'vie', 'sab', 'dom'])], 
            'activo'            => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_proveedor.required' => 'El nombre del proveedor es obligatorio',
            'id_tributaria.required'    => 'La ID tributaria es obligatoria',
            'id_tributaria.string'      => 'La ID tributaria debe ser un texto válido.',
            'id_tributaria.unique'      => 'La ID ya está registrada en el sistema.',
            'es_mayorista.required'     => 'Debe seleccionar si es mayorista o no',
            'frecuencia_visita.required'=> 'Debe seleccionar una frecuencia de visita.',
            'frecuencia_visita.in'      => 'La frecuencia de visita no es válida.',
            'cuando_entrega.required'   => 'Debe seleccionar un día de entrega.',
            'cuando_entrega.in'         => 'El día de entrega no es válido.',
            'cuando_viene.required'     => 'Debe seleccionar al menos un día de visita',
            'cuando_viene.array'        => 'El formato de días de visita no es válido.',
            'cuando_viene.*.in'         => 'Uno de los días de visita seleccionados no es válido.',
        ];
    }
}