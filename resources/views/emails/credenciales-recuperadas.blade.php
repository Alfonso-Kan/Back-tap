<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recuperación de contraseña</title>
</head>
<body style="font-family: Arial, sans-serif; color: #000000;">
    <h2>Recuperación de contraseña</h2>
    <p>Se generaron nuevas credenciales de acceso para tu cuenta en Tap Demo:</p>
    <p>
        <strong>Usuario:</strong> {{ $usuario }}<br>
        <strong>Nueva contraseña:</strong> {{ $password }}
    </p>
    <p>Te recomendamos iniciar sesión y cambiar tu contraseña cuanto antes.</p>
    <p>Gracias,<br>{{ config('app.name') }}</p>
</body>
</html>
