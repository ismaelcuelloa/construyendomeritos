<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {

        $data = $request->all();
        $data['role'] = User::ROLE_USER;

        $user = (new UserService)->create($data);

        event(new Registered($user));

        Auth::login($user);

        // Redirigir a la página de inicio para que el frontend maneje la redirección
        // basándose en checkout_return_url guardado en localStorage
        return redirect('/admin');
    }
}
