<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = auth('api')->user();

        if (!$user || !$user->tieneRol($role)) {
            return response()->json(['error' => 'Acceso denegado.'], 403);
        }

        return $next($request);
    }
}

