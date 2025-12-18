<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonalRequest;
use App\Http\Requests\UpdatePersonalRequest;
use App\Models\Personal;
use App\Models\Cargo;
use App\Models\Area;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PersonalController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('recursos-humanos.index');
    }

    public function store(StorePersonalRequest $request): RedirectResponse
    {
        Personal::create($request->validated());

        return redirect()->route('recursos-humanos.index')->with('success', 'Personal creado');
    }

    public function update(UpdatePersonalRequest $request, Personal $personal): RedirectResponse
    {
        $personal->update($request->validated());

        return redirect()->route('recursos-humanos.index')->with('success', 'Personal actualizado');
    }

    public function destroy(Personal $personal): RedirectResponse
    {
        $personal->delete();

        return redirect()->route('recursos-humanos.index')->with('success', 'Personal eliminado');
    }
}
