<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
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
            'nombre_cliente'      => 'required|string|max:255',
            'cc'   => 'required|numeric|unique:clientes,cc',
            'telefono_cliente'     => 'nullable|string|max:255',
            'activo'            => 'boolean' 

        ];
    }
    public function messages(): array
    {
        return [
            'nombre_cliente.required' => 'El nombre del cliente es obligatorio',
            'cc.required'   => 'La cc  es obligatoria',
            'cc.unique'     => 'La cc ya está registrada en el sistema.',
        ];
    }
}
