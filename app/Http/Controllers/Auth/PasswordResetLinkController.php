<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validar el email
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        // Rate limiting por IP para prevenir abuso
        $key = 'password-reset-'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => __('Demasiados intentos. Por favor intente de nuevo en :seconds segundos.', [
                    'seconds' => $seconds,
                ]),
            ]);
        }

        // Intentar enviar el enlace de restablecimiento
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Registrar el intento
        RateLimiter::hit($key, 60);

        // Log del intento
        Log::info('Password reset link requested', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $status,
        ]);

        // Siempre retornar el mismo mensaje para prevenir enumeración de usuarios
        return back()->with('status', __('Si existe una cuenta con ese correo, recibirás un enlace para restablecer tu contraseña.'));
    }
}
