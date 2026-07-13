<?php

namespace App\Http\Requests;

use App\Models\Seccion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:100', Rule::unique(Seccion::class, 'codigo')->ignore($this->route('id'), '_id')],
            'nombre' => ['required', 'string', 'max:255'],
        ];
    }
}
