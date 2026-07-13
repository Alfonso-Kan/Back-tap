<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'usuario' => ['required', 'email', Rule::unique(User::class, 'usuario')->ignore($this->route('id'), '_id')],
            'foto_perfil' => ['nullable', 'image', 'max:4096'],
            'telefono' => ['nullable', 'string', 'regex:/^\+[0-9]{1,3}[0-9\s]{4,14}$/'],
            'perfil_ids' => ['sometimes', 'array'],
            'perfil_ids.*' => ['string'],
        ];
    }
}
