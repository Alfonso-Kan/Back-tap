@extends('pdf.layout')

@section('titulo', 'Listado de Productos')

@section('contenido')
<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Marca</th>
            <th>Precio</th>
            <th>Fecha de creación</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productos as $producto)
        <tr>
            <td>{{ $producto->codigo }}</td>
            <td>{{ $producto->nombre }}</td>
            <td>{{ $producto->marca }}</td>
            <td>{{ number_format($producto->precio, 2) }}</td>
            <td>{{ optional($producto->created_at)->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
