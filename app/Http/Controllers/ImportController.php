<?php

namespace App\Http\Controllers;

use App\Jobs\ImportInventoryJob;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ImportController extends Controller
{
    /**
     * Handle an inventory Excel file upload and dispatch an import job.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'], // 20MB max
        ]);

        $file = $request->file('file');
        
        // Store the file temporarily
        $path = $file->storeAs('imports', 'inventory_' . time() . '.' . $file->getClientOriginalExtension());
        $fullPath = storage_path('app/' . $path);

        // Dispatch the job to the queue
        ImportInventoryJob::dispatch($fullPath, auth()->id());

        return response()->json([
            'message' => 'El archivo se ha enviado para procesamiento en segundo plano.',
            'status' => 'processing'
        ]);
    }
}
