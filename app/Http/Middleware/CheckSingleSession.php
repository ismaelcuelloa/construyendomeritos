<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = $request->session()->getId();

            // Verificar si la sesión actual coincide con la sesión registrada del usuario
            if ($user->current_session_id && $user->current_session_id !== $currentSessionId) {
                // La sesión ha sido reemplazada por otro dispositivo
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('status', 'Tu sesión ha sido cerrada porque iniciaste sesión en otro dispositivo.');
            }

            // Actualizar última actividad
            if ($user->last_activity_at === null || $user->last_activity_at->diffInMinutes(now()) > 5) {
                $user->update(['last_activity_at' => now()]);
            }
        }

        return $next($request);
    }
}
