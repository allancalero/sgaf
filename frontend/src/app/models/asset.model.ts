export interface Asset {
    id: number;
    codigo_inventario: string;
    nombre_activo: string;
    marca?: string;
    modelo?: string;
    color?: string;
    serie?: string;
    foto?: string;
    descripcion?: string;
    cantidad: number;
    precio_unitario: number;
    precio_adquisicion: number;
    fecha_adquisicion: string;
    numero_factura?: string;
    estado: 'BUENO' | 'REGULAR' | 'MALO';
    area_id: number;
    ubicacion_id: number;
    clasificacion_id: number;
    personal_id?: number;
    created_at: string;
    updated_at: string;
    // Relationships (simplified for list)
    area?: { nombre: string };
    personal?: { nombre: string; apellido: string };
    clasificacion?: { nombre: string };
}

export interface AssetResponse {
    data: Asset[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}
