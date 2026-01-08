export interface DashboardStats {
    totalActivos: number;
    totalPersonal: number;
    movimientosMes: number;
    sinAsignar: number;
    valorTotal: number;
    totalAreas: number;
    activosPorEstado: { estado: string; cantidad: number }[];
    activosPorArea: { area: string; cantidad: number; valor: number }[];
    responsablesPorArea: { area: string; cantidad: number }[];
    asignacionesPorMes: { mes: string; cantidad: number }[];
    activosPorClasificacion: { clasificacion: string; cantidad: number }[];
    valorPorArea: { area: string; valor: number }[];
}
