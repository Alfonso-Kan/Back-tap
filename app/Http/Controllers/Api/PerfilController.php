<?php

namespace App\Http\Controllers\Api;

use App\Exports\PerfilExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\PerfilRequest;
use App\Models\Perfil;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use OpenApi\Attributes as OA;

class PerfilController extends Controller
{
    #[OA\Get(
        path: '/api/perfiles',
        tags: ['Perfiles'],
        summary: 'Listar perfiles',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'buscar', in: 'query', required: false, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Listado de perfiles')],
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Perfil::query()->orderByDesc('created_at');

        if ($buscar = $request->query('buscar')) {
            $query->where('nombre', 'like', "%{$buscar}%");
        }

        return response()->json([
            'data' => $query->get()->map(fn (Perfil $perfil) => $this->presentar($perfil)),
        ]);
    }

    #[OA\Post(
        path: '/api/perfiles',
        tags: ['Perfiles'],
        summary: 'Crear perfil (código y fecha de creación se generan automáticamente)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['nombre', 'seccion_ids'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'seccion_ids', type: 'array', items: new OA\Items(type: 'string')),
            ],
        )),
        responses: [
            new OA\Response(response: 201, description: 'Perfil creado'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ],
    )]
    public function store(PerfilRequest $request): JsonResponse
    {
        $perfil = Perfil::create($request->validated());

        return response()->json($this->presentarDetalle($perfil), 201);
    }

    #[OA\Get(
        path: '/api/perfiles/{id}',
        tags: ['Perfiles'],
        summary: 'Detalle de perfil (secciones relacionadas)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Detalle del perfil')],
    )]
    public function show(string $id): JsonResponse
    {
        return response()->json($this->presentarDetalle(Perfil::findOrFail($id)));
    }

    #[OA\Put(
        path: '/api/perfiles/{id}',
        tags: ['Perfiles'],
        summary: 'Editar perfil',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['nombre', 'seccion_ids'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'seccion_ids', type: 'array', items: new OA\Items(type: 'string')),
            ],
        )),
        responses: [
            new OA\Response(response: 200, description: 'Perfil actualizado'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ],
    )]
    public function update(PerfilRequest $request, string $id): JsonResponse
    {
        $perfil = Perfil::findOrFail($id);
        $perfil->update($request->validated());

        return response()->json($this->presentarDetalle($perfil->fresh()));
    }

    #[OA\Delete(
        path: '/api/perfiles/{id}',
        tags: ['Perfiles'],
        summary: 'Eliminar perfil',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 204, description: 'Perfil eliminado')],
    )]
    public function destroy(string $id): Response
    {
        Perfil::findOrFail($id)->delete();

        return response()->noContent();
    }

    #[OA\Get(
        path: '/api/perfiles/export/pdf',
        tags: ['Perfiles'],
        summary: 'Descargar listado de perfiles en PDF',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Archivo PDF')],
    )]
    public function exportPdf()
    {
        $perfiles = Perfil::orderByDesc('created_at')->get();

        return Pdf::loadView('pdf.perfiles', ['perfiles' => $perfiles])->download('perfiles.pdf');
    }

    #[OA\Get(
        path: '/api/perfiles/export/excel',
        tags: ['Perfiles'],
        summary: 'Descargar listado de perfiles en Excel',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Archivo Excel')],
    )]
    public function exportExcel()
    {
        return Excel::download(new PerfilExport, 'perfiles.xlsx');
    }

    protected function presentar(Perfil $perfil): array
    {
        return [
            'id' => (string) $perfil->_id,
            'codigo' => $perfil->codigo,
            'nombre' => $perfil->nombre,
            'fecha_creacion' => optional($perfil->created_at)->toIso8601String(),
        ];
    }

    protected function presentarDetalle(Perfil $perfil): array
    {
        return array_merge($this->presentar($perfil), [
            'secciones' => $perfil->secciones()->map(fn ($s) => [
                'id' => (string) $s->_id,
                'codigo' => $s->codigo,
                'nombre' => $s->nombre,
            ])->values(),
        ]);
    }
}
