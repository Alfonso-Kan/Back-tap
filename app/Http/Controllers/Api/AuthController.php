<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Mail\CredencialesAcceso;
use App\Models\Perfil;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        tags: ['Auth'],
        summary: 'Iniciar sesión',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['usuario', 'password'],
            properties: [
                new OA\Property(property: 'usuario', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', format: 'password'),
            ],
        )),
        responses: [
            new OA\Response(response: 200, description: 'Token emitido y datos del usuario'),
            new OA\Response(response: 422, description: 'Credenciales incorrectas'),
        ],
    )]
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'usuario' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('usuario', $data['usuario'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'usuario' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->serializeUser($user),
        ]);
    }

    #[OA\Post(
        path: '/api/register',
        tags: ['Auth'],
        summary: 'Registrarse (se crea con perfil Administrador, acceso a todas las secciones)',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['nombre', 'usuario', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'usuario', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', format: 'password'),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                new OA\Property(property: 'telefono', type: 'string', description: 'Con código de país, ej. +52 5512345678'),
            ],
        )),
        responses: [
            new OA\Response(response: 201, description: 'Token emitido y datos del usuario'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ],
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $administrador = Perfil::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['seccion_ids' => Seccion::all()->pluck('_id')->map(fn ($id) => (string) $id)->all()],
        );

        $user = User::create([
            'nombre' => $data['nombre'],
            'usuario' => $data['usuario'],
            'password' => $data['password'],
            'telefono' => $data['telefono'] ?? null,
            'perfil_ids' => [(string) $administrador->_id],
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->serializeUser($user),
        ], 201);
    }

    #[OA\Post(
        path: '/api/logout',
        tags: ['Auth'],
        summary: 'Cerrar sesión (revoca el token actual)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Sesión cerrada')],
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    #[OA\Get(
        path: '/api/me',
        tags: ['Auth'],
        summary: 'Usuario autenticado y sus secciones accesibles',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Datos del usuario autenticado')],
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->serializeUser($request->user()));
    }

    #[OA\Post(
        path: '/api/forgot-password',
        tags: ['Auth'],
        summary: 'Recuperar contraseña (genera y envía nuevas credenciales por correo)',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['usuario'],
            properties: [new OA\Property(property: 'usuario', type: 'string', format: 'email')],
        )),
        responses: [
            new OA\Response(response: 200, description: 'Credenciales enviadas al correo registrado'),
            new OA\Response(response: 422, description: 'El usuario no existe'),
        ],
    )]
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'usuario' => ['required', 'email'],
        ]);

        $user = User::where('usuario', $data['usuario'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'usuario' => ['No existe un usuario registrado con ese correo.'],
            ]);
        }

        $nuevaPassword = Str::password(12);
        $user->update(['password' => $nuevaPassword]);

        Mail::to($user->usuario)->send(new CredencialesAcceso($user->usuario, $nuevaPassword));

        return response()->json(['message' => 'Se enviaron las nuevas credenciales al correo registrado.']);
    }

    protected function serializeUser(User $user): array
    {
        return [
            'id' => (string) $user->_id,
            'codigo' => $user->codigo,
            'nombre' => $user->nombre,
            'usuario' => $user->usuario,
            'telefono' => $user->telefono,
            'foto_perfil' => $user->foto_perfil ? Storage::disk('public')->url($user->foto_perfil) : null,
            'perfiles' => $user->perfiles()->map(fn ($p) => [
                'id' => (string) $p->_id,
                'codigo' => $p->codigo,
                'nombre' => $p->nombre,
            ])->values(),
            'secciones' => $user->seccionesAccesibles()->map(fn ($s) => [
                'id' => (string) $s->_id,
                'codigo' => $s->codigo,
                'nombre' => $s->nombre,
            ])->values(),
        ];
    }
}
