<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'marca' => ['required', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0', 'max:999.99'],
        ];
    }
}
