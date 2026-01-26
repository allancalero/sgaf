<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo_inventario' => $this->codigo_inventario,
            'nombre_activo' => $this->nombre_activo,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'serie' => $this->serie,
            'foto' => $this->foto, // Added missing field
            'estado' => $this->estado,
            'cantidad' => $this->cantidad,
            'precio_adquisicion' => (float) $this->precio_adquisicion,
            'fecha_adquisicion' => $this->fecha_adquisicion,
            'area_id' => $this->area_id,
            'personal_id' => $this->personal_id,
            'ubicacion_id' => $this->ubicacion_id,
            'clasificacion_id' => $this->clasificacion_id,
            'fuente_financiamiento_id' => $this->fuente_financiamiento_id,
            'area' => $this->whenLoaded('area', function() {
                return ['id' => $this->area->id, 'nombre' => $this->area->nombre];
            }),
            'personal' => $this->whenLoaded('personal', function() {
                return [
                    'id' => $this->personal->id, 
                    'nombre' => $this->personal->nombre,
                    'apellido' => $this->personal->apellido
                ];
            }),
            'clasificacion' => $this->whenLoaded('clasificacion', function() {
                return [
                    'id' => $this->clasificacion->id,
                    'nombre' => $this->clasificacion->nombre,
                    'codigo' => $this->clasificacion->codigo,
                    'prefijo' => $this->clasificacion->prefijo
                ];
            }),
            'ubicacion' => $this->whenLoaded('ubicacion', function() {
                return ['id' => $this->ubicacion->id, 'nombre' => $this->ubicacion->nombre];
            }),
            // Additional relations
            'fuente_financiamiento' => $this->whenLoaded('fuenteFinanciamiento', function() {
                return ['id' => $this->fuenteFinanciamiento->id, 'nombre' => $this->fuenteFinanciamiento->nombre];
            }),
             'created_at' => $this->created_at,
        ];
    }
}
