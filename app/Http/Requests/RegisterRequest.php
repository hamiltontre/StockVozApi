<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_negocio' => 'required|string|max:100',
            'nombre_admin'   => 'required|string|max:100',
            'email'          => 'required|email|unique:usuarios,email',
            'password'       => 'required|string|min:8|confirmed',
            'pin'            => 'required|digits:4',
            'telefono'       => 'nullable|string|max:30',
            'moneda'         => ['nullable', Rule::in(['NIO', 'USD'])],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'  => 'Este correo ya está registrado en StockVoz.',
            'pin.digits'    => 'El PIN debe tener exactamente 4 dígitos.',
            'password.min'  => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
