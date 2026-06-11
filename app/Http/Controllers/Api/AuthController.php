<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Autenticación para el dashboard web y registro de nuevos negocios.
 * La app móvil usa PIN local — para sincronizar emite un api_token
 * que se obtiene aquí con email + password.
 */
class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     * Crea un negocio nuevo con su primer usuario administrador.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $negocio = Negocio::create([
                'nombre'   => $data['nombre_negocio'],
                'email'    => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'moneda'   => $data['moneda'] ?? 'NIO',
                'plan'     => 'basico',
            ]);

            $salt = bin2hex(random_bytes(16));
            $pinHash = hash('sha256', $salt . $data['pin']);

            $admin = Usuario::create([
                'negocio_id' => $negocio->id,
                'nombre'     => $data['nombre_admin'],
                'email'      => $data['email'],
                'rol'        => 'admin',
                'password'   => $data['password'], // cast 'hashed' lo hashea
                'pin_hash'   => $pinHash,
                'salt'       => $salt,
                'activo'     => true,
            ]);

            // Token de acceso para la app móvil
            $token = $admin->createToken('app-movil', ['*'], now()->addYears(1))->plainTextToken;

            return response()->json([
                'mensaje' => 'Negocio registrado correctamente',
                'negocio' => $negocio,
                'usuario' => $admin->only(['id', 'nombre', 'email', 'rol']),
                'token'   => $token,
            ], 201);
        });
    }

    /**
     * POST /api/auth/login
     * Login con email + password para el dashboard web.
     * Devuelve un token Sanctum que también sirve para la app móvil.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting: máximo 5 intentos por email+IP por minuto (anti fuerza bruta)
        $key = 'login:' . Str::lower($data['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error'   => "Demasiados intentos. Espera {$seconds} segundos.",
                'seconds' => $seconds,
            ], 429);
        }

        $usuario = Usuario::where('email', $data['email'])->where('activo', true)->first();

        if (!$usuario || !Hash::check($data['password'], $usuario->password)) {
            RateLimiter::hit($key, 60); // ventana de 60 segundos
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        RateLimiter::clear($key);
        $usuario->update(['ultimo_acceso' => now()]);

        $token = $usuario->createToken('sesion-web')->plainTextToken;

        return response()->json([
            'usuario' => $usuario->only(['id', 'nombre', 'email', 'rol', 'negocio_id']),
            'negocio' => $usuario->negocio,
            'token'   => $token,
        ]);
    }

    /**
     * POST /api/auth/logout
     * Revoca el token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['mensaje' => 'Sesión cerrada']);
    }

    /**
     * GET /api/auth/me
     * Devuelve la info del usuario autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user();
        return response()->json([
            'usuario' => $usuario->only(['id', 'nombre', 'email', 'rol', 'negocio_id']),
            'negocio' => $usuario->negocio,
        ]);
    }
}
