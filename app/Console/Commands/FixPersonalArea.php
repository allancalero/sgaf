<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPersonalArea extends Command
{
    protected $signature = 'fix:personal-area';
    protected $description = 'Correccion de area_id en personal basado en activos asignados';

    public function handle()
    {
        $this->info("Corrigiendo Areas de Personal...");
        
        $personal = DB::table('personal')->get();
        $updates = 0;
        
        foreach ($personal as $p) {
            // Buscar el area del primer activo asignado a esta persona
            $activo = DB::table('activos_fijos')
                ->where('personal_id', $p->id)
                ->whereNotNull('area_id')
                ->first();
                
            if ($activo) {
                DB::table('personal')
                    ->where('id', $p->id)
                    ->update(['area_id' => $activo->area_id]);
                $updates++;
            }
        }
        
        $this->info("Actualizados {$updates} registros de personal.");
    }
}
