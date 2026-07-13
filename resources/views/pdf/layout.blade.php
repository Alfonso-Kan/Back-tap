<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>@yield('titulo')</title>
<style>
    body { font-family: 'DejaVu Sans', sans-serif; color: #000000; font-size: 12px; }
    h1 { color: #000000; border-bottom: 3px solid #F3D83E; padding-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #F3D83E; color: #000000; text-align: left; padding: 6px; }
    td { padding: 6px; border-bottom: 1px solid #dddddd; }
</style>
</head>
<body>
<h1>@yield('titulo')</h1>
@yield('contenido')
</body>
</html>
