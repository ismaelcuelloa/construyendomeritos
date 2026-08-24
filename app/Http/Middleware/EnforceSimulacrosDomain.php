<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceSimulacrosDomain
{
    /**
     * En el subdominio de simulacros, solo se permiten las rutas del simulacro.
     * Cualquier otra ruta redirige al dominio principal.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $simulacrosDomain = config('app.simulacros_domain');
        $mainDomain = config('app.domain');

        // Solo aplica si estamos en el subdominio y este es distinto del dominio principal
        if ($simulacrosDomain && $mainDomain && $host === $simulacrosDomain && $simulacrosDomain !== $mainDomain) {
            $path = trim($request->path(), '/');
            $firstSegment = explode('/', $path)[0] ?? '';

            // Rutas de simulacro permitidas en el subdominio
            $allowedSegments = ['examen', 'iniciar', 'guardar-progreso', 'enviar', 'resultados', 'logout', 'users'];

            // Rutas de assets estáticos que siempre deben cargar
            $assetSegments = ['build', 'assets', 'storage', 'favicon.ico', 'favicon.svg', 'manifest.json'];

            $isAllowed = $path === ''
                || in_array($firstSegment, $allowedSegments)
                || in_array($firstSegment, $assetSegments);

            if (! $isAllowed) {
                $mainUrl = rtrim($mainDomain, '/');
                $scheme = $request->isSecure() ? 'https://' : 'http://';
                $target = $scheme . $mainUrl . '/' . $path;

                return redirect()->away($target);
            }
        }

        return $next($request);
    }
}
