<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingController extends Controller
{
    public function index(): Response
    {
        $setting = SystemSetting::first();

        return Inertia::render('Sistema/Parametros', [
            'setting' => $setting,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_alcaldia' => ['nullable', 'string', 'max:255'],
            'alcaldesa' => ['nullable', 'string', 'max:255'],
            'gerente' => ['nullable', 'string', 'max:255'],
            'responsable_activo_fijo' => ['nullable', 'string', 'max:255'],
            'director_administrativo' => ['nullable', 'string', 'max:255'],
            'moneda' => ['required', 'string', 'max:20'],
            'ano_fiscal' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'logo_file' => ['nullable', 'image', 'mimes:png', 'max:2048'],
        ]);

        $setting = SystemSetting::firstOrNew([]);

        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('logos', 'public');
            $data['logo_url'] = Storage::disk('public')->url($path);
        }

        unset($data['logo_file']);

        $setting->fill($data);
        $setting->save();

        // Clear the cached system settings so changes appear immediately
        cache()->forget('system_setting:first');

        return redirect()->route('sistema.parametros')->with('success', 'Parámetros actualizados');
    }
}
