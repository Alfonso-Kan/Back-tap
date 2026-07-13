<?php

namespace App\Exports;

use App\Models\Perfil;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PerfilExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Perfil::orderByDesc('created_at')->get();
    }

    public function headings(): array
    {
        return ['Código', 'Nombre', 'Fecha de creación'];
    }

    public function map($perfil): array
    {
        return [
            $perfil->codigo,
            $perfil->nombre,
            optional($perfil->created_at)->format('d/m/Y H:i'),
        ];
    }
}
