import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ReporteService } from '../../services/reporte.service';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import Swal from 'sweetalert2';

interface Area { id: number; nombre: string; }
interface Clasificacion { id: number; nombre: string; prefijo: string; }
interface Ubicacion { id: number; nombre: string; }
interface Personal { id: number; nombre: string; apellido: string; area_id?: number; }

@Component({
    selector: 'app-reportes',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './reportes.component.html'
})
export class ReportesComponent implements OnInit {
    loading = false;

    // Filter data
    areas: Area[] = [];
    clasificaciones: Clasificacion[] = [];
    ubicaciones: Ubicacion[] = [];
    personal: Personal[] = [];
    allPersonal: Personal[] = [];

    // Stats data
    resumen: any = { total_activos: 0, valor_total: 0 };
    activosPorClasificacion: { clasificacion: string; cantidad: number }[] = [];
    maxClasificacion: number = 0;

    // Selected filters
    selectedAreaId: number | null = null;
    selectedClasificacionId: number | null = null;
    selectedUbicacionId: number | null = null;
    selectedPersonalId: number | null = null;
    selectedEstado: string | null = null;
    estados: string[] = ['BUENO', 'REGULAR', 'MALO'];

    constructor(
        private reporteService: ReporteService,
        private http: HttpClient,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.loadFilterData();
    }

    loadFilterData() {
        const apiUrl = environment.apiUrl;

        this.http.get<Area[]>(`${apiUrl}/areas/all`).subscribe({
            next: (data) => this.areas = data,
            error: (err) => console.error('Error loading areas:', err)
        });

        // Initial stats load without filters
        this.loadStats();

        this.http.get<Clasificacion[]>(`${apiUrl}/clasificaciones`).subscribe({
            next: (data) => this.clasificaciones = data,
            error: (err) => console.error('Error loading clasificaciones:', err)
        });

        this.http.get<Ubicacion[]>(`${apiUrl}/ubicaciones/all`).subscribe({
            next: (data) => this.ubicaciones = data,
            error: (err) => console.error('Error loading ubicaciones:', err)
        });

        this.http.get<Personal[]>(`${apiUrl}/personal/all`).subscribe({
            next: (data) => {
                this.allPersonal = data;
                this.filterPersonalByArea();
            },
            error: (err) => console.error('Error loading personal:', err)
        });
    }

    loadStats() {
        const apiUrl = environment.apiUrl;
        const params = new URLSearchParams();
        if (this.selectedAreaId) params.append('area_id', this.selectedAreaId.toString());
        if (this.selectedUbicacionId) params.append('ubicacion_id', this.selectedUbicacionId.toString());
        if (this.selectedPersonalId) params.append('personal_id', this.selectedPersonalId.toString());
        if (this.selectedClasificacionId) params.append('clasificacion_id', this.selectedClasificacionId.toString());
        if (this.selectedEstado) params.append('estado', this.selectedEstado);

        const queryString = params.toString();
        const suffix = queryString ? '?' + queryString : '';

        this.http.get<any>(`${apiUrl}/reportes/resumen${suffix}`).subscribe({
            next: (data) => {
                console.log('ReportesComponent: Resumen received:', data);
                this.resumen = data;
                this.cdr.detectChanges();
            },
            error: (err) => console.error('Error loading resumen:', err)
        });

        this.http.get<any[]>(`${apiUrl}/reportes/activos-por-clasificacion${suffix}`).subscribe({
            next: (data) => {
                console.log('ReportesComponent: Clasificacion data received:', data);
                this.activosPorClasificacion = data;
                this.maxClasificacion = Math.max(...data.map((d: any) => d.cantidad), 1);
                this.cdr.detectChanges();
            },
            error: (err) => console.error('Error loading stats:', err)
        });
    }

    onAreaChange() {
        this.selectedPersonalId = null; // Reset personal selection when area changes
        this.filterPersonalByArea();
        this.loadStats();
    }

    onFilterChange() {
        this.loadStats();
    }

    filterPersonalByArea() {
        if (this.selectedAreaId) {
            this.personal = this.allPersonal.filter(p => p.area_id === this.selectedAreaId);
        } else {
            this.personal = [...this.allPersonal];
        }
    }

    clearFilters() {
        this.selectedAreaId = null;
        this.selectedClasificacionId = null;
        this.selectedUbicacionId = null;
        this.selectedPersonalId = null;
        this.selectedEstado = null;
        this.filterPersonalByArea();
        this.loadStats();
    }

    hasActiveFilters(): boolean {
        return !!(this.selectedAreaId || this.selectedClasificacionId || this.selectedUbicacionId || this.selectedPersonalId || this.selectedEstado);
    }

    downloadReport(reportId: string) {
        this.loading = true;
        Swal.fire({
            title: 'Generando Reporte',
            text: 'Por favor espere mientras procesamos el documento...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const filters: any = {};
        if (this.selectedAreaId) filters.area_id = this.selectedAreaId;
        if (this.selectedEstado) filters.estado = this.selectedEstado;
        if (this.selectedClasificacionId) filters.clasificacion_id = this.selectedClasificacionId;
        if (this.selectedUbicacionId) filters.ubicacion_id = this.selectedUbicacionId;
        if (this.selectedPersonalId) filters.personal_id = this.selectedPersonalId;

        this.reporteService.descargarReporte(reportId, Object.keys(filters).length > 0 ? filters : undefined).subscribe({
            next: (blob) => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${reportId}_${new Date().toISOString().slice(0, 10)}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                this.loading = false;
                Swal.close();
                Swal.fire('Éxito', 'Reporte descargado correctamente', 'success');
                this.cdr.detectChanges();
            },
            error: () => {
                this.loading = false;
                Swal.close();
                Swal.fire('Error', 'No se pudo generar el reporte en este momento', 'error');
                this.cdr.detectChanges();
            }
        });
    }
}
