<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BitacoraController extends Controller
{
    #[OA\Get(
        path: '/api/bitacora',
        tags: ['Bitácora'],
        summary: 'Listar bitácora de auditoría (datos anteriores vs. actuales)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'coleccion', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'usuarios|perfiles|secciones|productos'),
            new OA\Parameter(name: 'accion', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'creacion|actualizacion|eliminacion'),
        ],
        responses: [new OA\Response(response: 200, description: 'Listado de la bitácora')],
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Bitacora::query()->orderByDesc('fecha');

        if ($coleccion = $request->query('coleccion')) {
            $query->where('coleccion', $coleccion);
        }

        if ($accion = $request->query('accion')) {
            $query->where('accion', $accion);
        }

        return response()->json([
            'data' => $query->get()->map(fn (Bitacora $bitacora) => $this->presentar($bitacora)),
        ]);
    }

    #[OA\Get(
        path: '/api/bitacora/{id}',
        tags: ['Bitácora'],
        summary: 'Detalle de un registro de bitácora',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Detalle del registro')],
    )]
    public function show(string $id): JsonResponse
    {
        return response()->json($this->presentar(Bitacora::findOrFail($id)));
    }

    protected function presentar(Bitacora $bitacora): array
    {
        $usuario = $bitacora->usuario();

        return [
            'id' => (string) $bitacora->_id,
            'coleccion' => $bitacora->coleccion,
            'documento_id' => $bitacora->documento_id,
            'accion' => $bitacora->accion,
            'datos_anteriores' => $bitacora->datos_anteriores,
            'datos_nuevos' => $bitacora->datos_nuevos,
            'usuario' => $usuario ? [
                'id' => (string) $usuario->_id,
                'usuario' => $usuario->usuario,
                'nombre' => $usuario->nombre,
            ] : null,
            'fecha' => optional($bitacora->fecha)->toIso8601String(),
        ];
    }
}
