<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'forceLogout' => $request->query('force_logout', false),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Convertir force_logout a booleano correctamente
        $forceLogout = filter_var($request->input('force_logout', false), FILTER_VALIDATE_BOOLEAN);

        // Log para depuración
        \Log::info('Login attempt', [
            'email' => $request->email,
            'force_logout' => $forceLogout,
            'force_logout_raw' => $request->input('force_logout'),
        ]);

        $user = User::where('email', $request->email)->first();

        // Si force_logout es true, cerrar sesión anterior y continuar
        if ($forceLogout) {
            \Log::info('Force logout requested - closing previous session');
            if ($user) {
                \Log::info('Logging out previous session', [
                    'user_id' => $user->id,
                    'session_id' => $user->current_session_id,
                ]);
                $this->logoutPreviousSession($user, $request->session()->getId());
            }

            // Evitar bloqueo por rate limiting en el flujo de force logout
            \Illuminate\Support\Facades\RateLimiter::clear($request->throttleKey());

            // Proceder directamente con autenticación sin verificar sesión activa
            $request->authenticate();
            $request->session()->regenerate();

            // Actualizar información de sesión del usuario
            $authenticatedUser = Auth::user();
            $authenticatedUser->update([
                'current_session_id' => $request->session()->getId(),
                'current_device_info' => $this->getDeviceInfo($request),
                'last_activity_at' => now(),
            ]);

            \Log::info('Login successful with force logout', [
                'user_id' => $authenticatedUser->id,
                'new_session_id' => $request->session()->getId(),
            ]);

            $route = 'mis_cursos';
            if (Auth::user()->hasRole([User::ROLE_SUPER_USER, User::ROLE_ADMIN])) {
                $route = 'admin';
            }

            return redirect($route)->with('X-Inertia-Location', url($route));
        }

        // Si NO es force_logout, validar credenciales y verificar sesión activa
        // Verificar credenciales manualmente primero
        if ($user && \Hash::check($request->password, $user->password)) {
            // Credenciales válidas - verificar si hay sesión activa
            if ($user->current_session_id) {
                $sessionExists = \DB::table('sessions')
                    ->where('id', $user->current_session_id)
                    ->exists();

                if ($sessionExists) {
                    \Log::info('Active session detected', [
                        'user_id' => $user->id,
                        'current_session_id' => $user->current_session_id,
                    ]);

                    // Hay una sesión activa - mostrar diálogo
                    return back()->withErrors([
                        'active_session' => true,
                        'device_info' => $user->current_device_info,
                        'current_device' => $this->getDeviceInfo($request),
                        'last_activity' => $user->last_activity_at?->diffForHumans(),
                    ])->onlyInput('email');
                }
            }
        }

        // Proceder con autenticación normal
        $request->authenticate();
        $request->session()->regenerate();

        // Actualizar información de sesión del usuario
        $authenticatedUser = Auth::user();
        $authenticatedUser->update([
            'current_session_id' => $request->session()->getId(),
            'current_device_info' => $this->getDeviceInfo($request),
            'last_activity_at' => now(),
        ]);

        \Log::info('Login successful', [
            'user_id' => $authenticatedUser->id,
            'new_session_id' => $request->session()->getId(),
        ]);

        $route = 'mis_cursos';
        if (Auth::user()->hasRole([User::ROLE_SUPER_USER, User::ROLE_ADMIN])) {
            $route = 'admin';
        }

        // Forzar una carga completa de la página para actualizar datos del usuario
        return redirect($route)->with('X-Inertia-Location', url($route));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Limpiar información de sesión del usuario
        if ($user) {
            $user->update([
                'current_session_id' => null,
                'current_device_info' => null,
                'last_activity_at' => null,
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flush();

        // Redirigir al dominio principal (borra cookies de sesión de ambos dominios)
        $mainDomain = config('app.domain');
        if ($mainDomain) {
            $scheme = $request->isSecure() ? 'https://' : 'http://';
            $target = $scheme . $mainDomain . '/';

            return redirect()->away($target, 302, ['X-Inertia-Location' => $target]);
        }

        return redirect('/');
    }

    /**
     * Get device information from request
     */
    private function getDeviceInfo(Request $request): string
    {
        $agent = new \Jenssegers\Agent\Agent;
        $agent->setUserAgent($request->userAgent());

        $device = $agent->device() ?: 'Desconocido';
        $platform = $agent->platform();
        $browser = $agent->browser();

        return "{$device} - {$platform} ({$browser})";
    }

    /**
     * Logout previous session
     */
    private function logoutPreviousSession(User $user, ?string $currentSessionId = null): void
    {
        if ($user->current_session_id && $user->current_session_id !== $currentSessionId) {
            // Eliminar la sesión anterior de la tabla de sesiones
            \DB::table('sessions')
                ->where('id', $user->current_session_id)
                ->delete();
        }
    }
}
