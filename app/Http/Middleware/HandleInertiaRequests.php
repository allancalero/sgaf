<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\SystemSetting;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        try {
            $setting = cache()->remember('system_setting:first', 60, fn () => SystemSetting::first());
        } catch (\Throwable $e) {
            // Log the error if needed, but allow the app to proceed with default settings
            // Log::error('Failed to fetch system settings: ' . $e->getMessage());
            $setting = null;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'nombre' => $request->user()->nombre,
                        'apellido' => $request->user()->apellido,
                        'email' => $request->user()->email,
                        'rol' => $request->user()->rol,
                        'estado' => $request->user()->estado,
                        'foto' => $request->user()->foto,
                        'full_name' => $request->user()->full_name,
                        'roles' => $request->user()->getRoleNames(),
                        'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                    ]
                    : null,
            ],
            'system' => $setting ? [
                'nombre_alcaldia' => $setting->nombre_alcaldia,
                'alcaldesa' => $setting->alcaldesa,
                'gerente' => $setting->gerente,
                'moneda' => $setting->moneda,
                'ano_fiscal' => $setting->ano_fiscal,
                'logo_url' => $setting->logo_url,
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
