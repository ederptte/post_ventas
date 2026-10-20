<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
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
        $clienteId = $this->route('cliente');
        return [
            'nombre_cliente'      => 'sometimes|required|string|max:255',
            'cc'         => 'sometimes|numeric|unique:clientes,cc,'. $clienteId,

            'telefono_vendedor'     => 'nullable|string|max:255',

            'activo'            => 'boolean'
            

        ];
    }
    public function messages(): array
    {
        return [
            'nombre_cliente.required' => 'El nombre del cliente es obligatorio',
            'cc.required'   => 'La cc es obligatoria',
            'cc.unique'     => 'La cc ya está registrada en el sistema.',
        ];
    }
}
