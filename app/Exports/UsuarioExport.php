<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsuarioExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return User::orderByDesc('created_at')->get();
    }

    public function headings(): array
    {
        return ['Código', 'Usuario', 'Nombre', 'Fecha de creación'];
    }

    public function map($user): array
    {
        return [
            $user->codigo,
            $user->usuario,
            $user->nombre,
            optional($user->created_at)->format('d/m/Y H:i'),
        ];
    }
}
