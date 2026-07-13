@extends('pdf.layout')

@section('titulo', 'Listado de Perfiles')

@section('contenido')
<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Fecha de creación</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($perfiles as $perfil)
        <tr>
            <td>{{ $perfil->codigo }}</td>
            <td>{{ $perfil->nombre }}</td>
            <td>{{ optional($perfil->created_at)->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
