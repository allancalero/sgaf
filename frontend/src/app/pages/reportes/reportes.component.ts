import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { ReporteService } from '../../services/reporte.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-reportes',
    standalone: true,
    imports: [CommonModule, MainLayoutComponent],
    templateUrl: './reportes.component.html'
})
export class ReportesComponent implements OnInit {
    loading = false;
    reports = [
        {
            id: 'inventario-general',
            title: 'Inventario General',
            description: 'Listado completo de todos los activos fijos registrados en el sistema con su estado actual y ubicación.',
            icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'
        },
        {
            id: 'activos-por-area',
            title: 'Activos por Área',
            description: 'Reporte detallado que agrupa los activos según el departamento o área administrativa asignada.',
            icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        },
        {
            id: 'depreciacion-acumulada',
            title: 'Depreciación Acumulada',
            description: 'Cálculo contable de la pérdida de valor de los activos a través del tiempo según su categoría.',
            icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1m0-1v-8m4 0h.01M4 20h16a2 2 0 002-2V6a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z'
        },
        {
            id: 'historial-asignaciones',
            title: 'Historial de Asignaciones',
            description: 'Trazabilidad completa de movimientos y cambios de custodios de los activos municipales.',
            icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
        },
        {
            id: 'activos-en-mal-estado',
            title: 'Activos de Baja/Mal Estado',
            description: 'Resumen de activos retirados o que requieren mantenimiento correctivo prioritario.',
            icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
        },
        {
            id: 'personal-con-activos',
            title: 'Responsabilidad por Persona',
            description: 'Actas de entrega y listado de bienes que cada trabajador tiene bajo su responsabilidad.',
            icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
        }
    ];

    constructor(
        private reporteService: ReporteService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() { }

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

        this.reporteService.descargarReporte(reportId).subscribe({
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
