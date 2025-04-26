<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!in_array(Auth::user()->role, $roles)) {
            return redirect()->route('dashboard'); // Redireciona para o dashboard, ou outra página
        }
        return $next($request);
    }
}

