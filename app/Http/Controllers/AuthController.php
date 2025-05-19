<?php

namespace App\Http\Controllers;

use App\Models\Usuarios;
use App\Models\UsuarioSesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    const ESTADO_ACTIVO = true;
    const ESTADO_INACTIVO = false;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refreshToken']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:usuarios,email',
            'password' => 'required|string|min:6|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = Usuarios::where('email', $request->email)->first();

        $customClaims = [
            'roles' => $user->roles()->pluck('nombre'),
        ];

        if (!$token = auth('api')->claims($customClaims)->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        UsuarioSesion::where('id_usuario', $user->id)
            ->where('activo', self::ESTADO_ACTIVO)
            ->whereNull('closed_at')
            ->update([
                'activo' => self::ESTADO_INACTIVO,
                'closed_at' => now(),
            ]);

        $user->ultima_sesion = now();
        $user->save();

        $refreshToken = $this->generarRefreshToken($user);

        return $this->respondWithToken($token, $refreshToken);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        UsuarioSesion::where('id_usuario', $user->id)
            ->where('activo', self::ESTADO_ACTIVO)
            ->update([
                'activo' => self::ESTADO_INACTIVO,
                'closed_at' => now(),
            ]);

        auth('api')->logout();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $refreshToken = $request->refresh_token;

        $session = UsuarioSesion::where('token', $refreshToken)
            ->where('activo', self::ESTADO_ACTIVO)
            ->where('expires_at', '>', now())
            ->first();

        if (!$session) {
            return response()->json(['message' => 'Refresh token inválido o expirado'], 400);
        }

        $user = Usuarios::find($session->id_usuario);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $customClaims = [
            'roles' => $user->roles()->pluck('nombre'),
        ];

        $token = JWTAuth::claims($customClaims)->fromUser($user);

        UsuarioSesion::where('activo', self::ESTADO_ACTIVO)
            ->where('expires_at', '<=', now())
            ->update([
                'activo' => self::ESTADO_INACTIVO,
                'closed_at' => now(),
            ]);

        $newRefreshToken = $this->generarRefreshToken($user);

        return $this->respondWithToken($token, $newRefreshToken);
    }

    protected function respondWithToken($token, $refreshToken = null)
    {
        return response()->json([
            'access_token'  => $token,
            'token_type'    => 'bearer',
            'expires_in'    => auth('api')->factory()->getTTL() * 60,
            'refresh_token' => $refreshToken,
        ]);
    }

    private function generarRefreshToken(Usuarios $user)
    {
        $refreshToken = Str::uuid()->toString();
        $expiresAt = now()->addMinutes(env('REFRESH_TOKEN_TTL', 60));

        UsuarioSesion::create([
            'id_usuario' => $user->id,
            'token'      => $refreshToken,
            'activo'     => self::ESTADO_ACTIVO,
            'expires_at' => $expiresAt,
            'closed_at'  => null,
        ]);

        return $refreshToken;
    }
}
