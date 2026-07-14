<?php

namespace App\Http\Controllers\Api;

use App\Exports\UsuarioExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Mail\CredencialesAcceso;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use OpenApi\Attributes as OA;

class UsuarioController extends Controller
{
    #[OA\Get(
        path: '/api/usuarios',
        tags: ['Usuarios'],
        summary: 'Listar usuarios',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'buscar', in: 'query', required: false, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Listado de usuarios')],
    )]
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->orderByDesc('created_at');

        if ($buscar = $request->query('buscar')) {
            $query->where('nombre', 'like', "%{$buscar}%");
        }

        return response()->json([
            'data' => $query->get()->map(fn (User $user) => $this->presentar($user)),
        ]);
    }

    #[OA\Post(
        path: '/api/usuarios',
        tags: ['Usuarios'],
        summary: 'Crear usuario (código y fecha de creación se generan automáticamente; se envía una contraseña inicial por correo)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['nombre', 'usuario', 'foto_perfil'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
                    new OA\Property(property: 'usuario', type: 'string', format: 'email'),
                    new OA\Property(property: 'foto_perfil', type: 'string', format: 'binary'),
                    new OA\Property(property: 'telefono', type: 'string', description: 'Con código de país, ej. +52 5512345678'),
                    new OA\Property(property: 'perfil_ids[]', type: 'array', items: new OA\Items(type: 'string')),
                ],
            ),
        )),
        responses: [
            new OA\Response(response: 201, description: 'Usuario creado'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ],
    )]
    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['foto_perfil'] = $request->file('foto_perfil')->store('avatars', 'public');

        $passwordGenerada = Str::password(12);
        $data['password'] = $passwordGenerada;

        $user = User::create($data);

        $correoEnviado = true;

        try {
            Mail::to($user->usuario)->send(new CredencialesAcceso($user->usuario, $passwordGenerada, esNuevoUsuario: true));
        } catch (\Throwable $e) {
            $correoEnviado = false;
            Log::warning("No se pudo enviar el correo de credenciales a {$user->usuario}: {$e->getMessage()}");
        }

        return response()->json(array_merge($this->presentarDetalle($user), [
            'correo_enviado' => $correoEnviado,
        ]), 201);
    }

    #[OA\Get(
        path: '/api/usuarios/{id}',
        tags: ['Usuarios'],
        summary: 'Detalle de usuario (usuario, nombre, teléfono, foto, perfiles relacionados)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Detalle del usuario')],
    )]
    public function show(string $id): JsonResponse
    {
        return response()->json($this->presentarDetalle(User::findOrFail($id)));
    }

    #[OA\Put(
        path: '/api/usuarios/{id}',
        tags: ['Usuarios'],
        summary: 'Editar usuario (enviar _method=PUT junto con multipart/form-data si se reemplaza la foto)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['nombre', 'usuario'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string'),
                    new OA\Property(property: 'usuario', type: 'string', format: 'email'),
                    new OA\Property(property: 'foto_perfil', type: 'string', format: 'binary'),
                    new OA\Property(property: 'telefono', type: 'string'),
                    new OA\Property(property: 'perfil_ids[]', type: 'array', items: new OA\Items(type: 'string')),
                ],
            ),
        )),
        responses: [
            new OA\Response(response: 200, description: 'Usuario actualizado'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ],
    )]
    public function update(UpdateUsuarioRequest $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->usuario === 'admin@tapdemo.com') {
            abort(403, 'El usuario admin@tapdemo.com no se puede editar.');
        }

        $data = $request->validated();

        if ($request->hasFile('foto_perfil')) {
            if ($user->foto_perfil) {
                Storage::disk('public')->delete($user->foto_perfil);
            }
            $data['foto_perfil'] = $request->file('foto_perfil')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json($this->presentarDetalle($user->fresh()));
    }

    #[OA\Delete(
        path: '/api/usuarios/{id}',
        tags: ['Usuarios'],
        summary: 'Eliminar usuario',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 204, description: 'Usuario eliminado')],
    )]
    public function destroy(string $id): Response
    {
        $user = User::findOrFail($id);

        if ($user->usuario === 'admin@tapdemo.com') {
            abort(403, 'El usuario admin@tapdemo.com no se puede eliminar.');
        }

        if ($user->foto_perfil) {
            Storage::disk('public')->delete($user->foto_perfil);
        }

        $user->delete();

        return response()->noContent();
    }

    #[OA\Get(
        path: '/api/usuarios/export/pdf',
        tags: ['Usuarios'],
        summary: 'Descargar listado de usuarios en PDF',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Archivo PDF')],
    )]
    public function exportPdf()
    {
        $usuarios = User::orderByDesc('created_at')->get();

        return Pdf::loadView('pdf.usuarios', ['usuarios' => $usuarios])->download('usuarios.pdf');
    }

    #[OA\Get(
        path: '/api/usuarios/export/excel',
        tags: ['Usuarios'],
        summary: 'Descargar listado de usuarios en Excel',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Archivo Excel')],
    )]
    public function exportExcel()
    {
        return Excel::download(new UsuarioExport, 'usuarios.xlsx');
    }

    protected function presentar(User $user): array
    {
        return [
            'id' => (string) $user->_id,
            'codigo' => $user->codigo,
            'usuario' => $user->usuario,
            'nombre' => $user->nombre,
            'fecha_creacion' => optional($user->created_at)->toIso8601String(),
            'protegido' => $user->usuario === 'admin@tapdemo.com',
        ];
    }

    protected function presentarDetalle(User $user): array
    {
        return array_merge($this->presentar($user), [
            'telefono' => $user->telefono,
            'foto_perfil' => $user->foto_perfil ? Storage::disk('public')->url($user->foto_perfil) : null,
            'perfiles' => $user->perfiles()->map(fn ($p) => [
                'id' => (string) $p->_id,
                'codigo' => $p->codigo,
                'nombre' => $p->nombre,
            ])->values(),
        ]);
    }
}
