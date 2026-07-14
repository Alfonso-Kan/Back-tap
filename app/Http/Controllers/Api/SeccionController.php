<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeccionRequest;
use App\Models\Seccion;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SeccionController extends Controller
{
    #[OA\Get(
        path: '/api/secciones',
        tags: ['Secciones'],
        summary: 'Listar secciones',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Listado de secciones')],
    )]
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Seccion::orderBy('nombre')->get()->map(fn (Seccion $seccion) => $this->presentar($seccion)),
        ]);
    }

    #[OA\Get(
        path: '/api/secciones/{id}',
        tags: ['Secciones'],
        summary: 'Ver detalle de una sección',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Detalle de la sección')],
    )]
    public function show(string $id): JsonResponse
    {
        return response()->json($this->presentar(Seccion::findOrFail($id)));
    }

    #[OA\Put(
        path: '/api/secciones/{id}',
        tags: ['Secciones'],
        summary: 'Editar el nombre de una sección (el código es fijo, no editable)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['nombre'],
            properties: [new OA\Property(property: 'nombre', type: 'string')],
        )),
        responses: [
            new OA\Response(response: 200, description: 'Sección actualizada'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ],
    )]
    public function update(SeccionRequest $request, string $id): JsonResponse
    {
        $seccion = Seccion::findOrFail($id);
        $seccion->update($request->validated());

        return response()->json($this->presentar($seccion->fresh()));
    }

    protected function presentar(Seccion $seccion): array
    {
        return [
            'id' => (string) $seccion->_id,
            'codigo' => $seccion->codigo,
            'nombre' => $seccion->nombre,
            'fecha_creacion' => optional($seccion->created_at)->toIso8601String(),
        ];
    }
}
