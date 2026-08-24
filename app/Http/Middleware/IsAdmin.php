<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        // Permitir acceso a admin o super_user
        if (! auth()->user()->hasRole(['admin', 'super_user'])) {
            return redirect('/')->with('error', 'No tienes permisos para acceder a esta área');
        }

        return $next($request);
    }
}
