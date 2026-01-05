<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::with(['user:id,nombre,apellido,email'])
            ->orderBy('created_at', 'desc');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('event')) {
            $query->where('event', 'like', '%' . $request->event . '%');
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $audits = $query->paginate(20)->withQueryString();

        $users = User::orderBy('nombre')->get(['id', 'nombre', 'apellido']);

        return Inertia::render('Sistema/Auditoria', [
            'audits' => $audits,
            'filters' => $request->all(['user_id', 'event', 'date_from', 'date_to']),
            'users' => $users,
        ]);
    }
}
