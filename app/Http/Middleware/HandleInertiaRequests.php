<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        // Si el usuario está autenticado, cargar sus datos completos con roles
        if ($user) {
            $user->load('roles');
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
            ],
            'seo' => [
                'title' => 'Materiales de estudio - Procuraduría General de la Nación 2026 | Construyendo Méritos con Excelencia',
                'description' => 'Materiales de estudio para la Procuraduría General de la Nación 2026 - Construyendo Méritos con Excelencia. Simulacros y guías actualizadas para tu preparación.',
                'image' => asset('assets/images/logo/logo-default.png'),
                'url' => $request->url(),
                'type' => 'website',
            ],
            'simulacrosUrl' => config('app.simulacros_url', '/simulacros'),
            'mainDomain' => config('app.domain'),
            'simulacrosDomain' => config('app.simulacros_domain'),
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
