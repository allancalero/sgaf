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
        $setting = cache()->remember('system_setting:first', 60, fn () => SystemSetting::first());

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
                'moneda' => $setting->moneda,
                'ano_fiscal' => $setting->ano_fiscal,
                'logo_url' => $setting->logo_url,
            ] : null,
        ];
    }
}
