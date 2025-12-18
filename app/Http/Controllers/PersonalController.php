<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonalRequest;
use App\Http\Requests\UpdatePersonalRequest;
use App\Models\Personal;
use App\Models\Cargo;
use App\Models\Area;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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
        $data = $request->validated();
        
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('personal', 'public');
        }
        
        Personal::create($data);

        return redirect()->route('recursos-humanos.index')->with('success', 'Personal creado');
    }

    public function update(UpdatePersonalRequest $request, Personal $personal): RedirectResponse
    {
        $data = $request->validated();
        
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($personal->foto) {
                Storage::disk('public')->delete($personal->foto);
            }
            $data['foto'] = $request->file('foto')->store('personal', 'public');
        } else {
            // Keep existing photo
            unset($data['foto']);
        }
        
        $personal->update($data);

        return redirect()->route('recursos-humanos.index')->with('success', 'Personal actualizado');
    }

    public function destroy(Personal $personal): RedirectResponse
    {
        // Delete photo if exists
        if ($personal->foto) {
            Storage::disk('public')->delete($personal->foto);
        }
        
        $personal->delete();

        return redirect()->route('recursos-humanos.index')->with('success', 'Personal eliminado');
    }
}

