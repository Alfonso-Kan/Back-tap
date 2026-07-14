<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $esNuevoUsuario ? 'Bienvenido a Tap Demo' : 'Recuperación de contraseña' }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #000000;">
    @if ($esNuevoUsuario)
        <h2>Bienvenido a Tap Demo</h2>
        <p>Se creó tu cuenta en Tap Demo con las siguientes credenciales de acceso:</p>
    @else
        <h2>Recuperación de contraseña</h2>
        <p>Se generaron nuevas credenciales de acceso para tu cuenta en Tap Demo:</p>
    @endif
    <p>
        <strong>Usuario:</strong> {{ $usuario }}<br>
        <strong>Contraseña:</strong> {{ $password }}
    </p>
    <p>Te recomendamos iniciar sesión y cambiar tu contraseña cuanto antes.</p>
    <p>Gracias,<br>{{ config('app.name') }}</p>
</body>
</html>
