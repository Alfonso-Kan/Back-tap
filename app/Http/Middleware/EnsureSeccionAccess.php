<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSeccionAccess
{
    /**
     * Blocks the request unless the authenticated user has a perfil granting
     * access to the given seccion codigo (e.g. 'productos', 'usuarios').
     */
    public function handle(Request $request, Closure $next, string $codigo): Response
    {
        $user = $request->user();

        if (! $user || ! $user->tieneAccesoASeccion($codigo)) {
            abort(403, 'No tiene acceso a esta sección.');
        }

        return $next($request);
    }
}
