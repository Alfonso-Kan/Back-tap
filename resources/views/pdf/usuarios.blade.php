@extends('pdf.layout')

@section('titulo', 'Listado de Usuarios')

@section('contenido')
<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Usuario</th>
            <th>Nombre</th>
            <th>Fecha de creación</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($usuarios as $usuario)
        <tr>
            <td>{{ $usuario->codigo }}</td>
            <td>{{ $usuario->usuario }}</td>
            <td>{{ $usuario->nombre }}</td>
            <td>{{ optional($usuario->created_at)->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
