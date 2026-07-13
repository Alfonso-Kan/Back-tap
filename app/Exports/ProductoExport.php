<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductoExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Producto::orderByDesc('created_at')->get();
    }

    public function headings(): array
    {
        return ['Código', 'Nombre', 'Marca', 'Precio', 'Fecha de creación'];
    }

    public function map($producto): array
    {
        return [
            $producto->codigo,
            $producto->nombre,
            $producto->marca,
            $producto->precio,
            optional($producto->created_at)->format('d/m/Y H:i'),
        ];
    }
}
