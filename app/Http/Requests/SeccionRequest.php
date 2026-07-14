<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Secciones are the app's fixed set of screens (each has a matching Angular
 * route and an `EnsureSeccionAccess` middleware group keyed by codigo), so
 * they can't be freely created/deleted from the UI — only the display
 * nombre is editable here. codigo is immutable once seeded.
 */
class SeccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
        ];
    }
}
