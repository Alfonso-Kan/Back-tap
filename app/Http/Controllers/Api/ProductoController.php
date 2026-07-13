<?php

namespace App\Http\Controllers\Api;

use App\Exports\ProductoExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductoRequest;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use OpenApi\Attributes as OA;

class ProductoController extends Controller
{
    #[OA\Get(
        path: '/api/productos',
        tags: ['Productos'],
        summary: 'Listar productos',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'buscar', in: 'query', required: false, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Listado de productos')],
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Producto::query()->orderByDesc('created_at');

        if ($buscar = $request->query('buscar')) {
            $query->where('nombre', 'like', "%{$buscar}%");
        }

        return response()->json([
            'data' => $query->get()->map(fn (Producto $producto) => $this->presentar($producto)),
        ]);
    }

    #[OA\Post(
        path: '/api/productos',
        tags: ['Productos'],
        summary: 'Crear producto (código y fecha de creación se generan automáticamente)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['nombre', 'marca', 'precio'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'marca', type: 'string'),
                new OA\Property(property: 'precio', type: 'number', format: 'float', maximum: 999.99),
            ],
        )),
        responses: [
            new OA\Response(response: 201, description: 'Producto creado'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ],
    )]
    public function store(ProductoRequest $request): JsonResponse
    {
        $producto = Producto::create($request->validated());

        return response()->json($this->presentar($producto), 201);
    }

    #[OA\Get(
        path: '/api/productos/{id}',
        tags: ['Productos'],
        summary: 'Ver detalle de un producto',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Detalle del producto')],
    )]
    public function show(string $id): JsonResponse
    {
        return response()->json($this->presentar(Producto::findOrFail($id)));
    }

    #[OA\Put(
        path: '/api/productos/{id}',
        tags: ['Productos'],
        summary: 'Editar producto',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['nombre', 'marca', 'precio'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'marca', type: 'string'),
                new OA\Property(property: 'precio', type: 'number', format: 'float', maximum: 999.99),
            ],
        )),
        responses: [
            new OA\Response(response: 200, description: 'Producto actualizado'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ],
    )]
    public function update(ProductoRequest $request, string $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);
        $producto->update($request->validated());

        return response()->json($this->presentar($producto->fresh()));
    }

    #[OA\Delete(
        path: '/api/productos/{id}',
        tags: ['Productos'],
        summary: 'Eliminar producto',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 204, description: 'Producto eliminado')],
    )]
    public function destroy(string $id): Response
    {
        Producto::findOrFail($id)->delete();

        return response()->noContent();
    }

    #[OA\Get(
        path: '/api/productos/export/pdf',
        tags: ['Productos'],
        summary: 'Descargar listado de productos en PDF',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Archivo PDF')],
    )]
    public function exportPdf()
    {
        $productos = Producto::orderByDesc('created_at')->get();

        return Pdf::loadView('pdf.productos', ['productos' => $productos])->download('productos.pdf');
    }

    #[OA\Get(
        path: '/api/productos/export/excel',
        tags: ['Productos'],
        summary: 'Descargar listado de productos en Excel',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Archivo Excel')],
    )]
    public function exportExcel()
    {
        return Excel::download(new ProductoExport, 'productos.xlsx');
    }

    protected function presentar(Producto $producto): array
    {
        return [
            'id' => (string) $producto->_id,
            'codigo' => $producto->codigo,
            'nombre' => $producto->nombre,
            'marca' => $producto->marca,
            'precio' => $producto->precio,
            'fecha_creacion' => optional($producto->created_at)->toIso8601String(),
        ];
    }
}
