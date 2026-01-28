import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AssetService } from '../../services/asset.service';
import Swal from 'sweetalert2';
import { FormsModule } from '@angular/forms';

@Component({
    selector: 'app-solicitudes',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './solicitudes.component.html',
    styleUrls: ['./solicitudes.component.css'] // Optional if I create it
})
export class SolicitudesComponent implements OnInit {
    solicitudes: any[] = [];
    loading = true;
    filterEstado = 'PENDIENTE';

    constructor(private assetService: AssetService) { }

    ngOnInit() {
        this.loadSolicitudes();
    }

    loadSolicitudes() {
        this.loading = true;
        this.assetService.getSolicitudes(this.filterEstado).subscribe({
            next: (res: any) => {
                this.solicitudes = res.data;
                this.loading = false;
            },
            error: (err) => {
                console.error(err);
                this.loading = false;
            }
        });
    }

    aprobar(id: number) {
        Swal.fire({
            title: '¿Aprobar Baja?',
            text: "El activo será eliminado permanentemente. Esta acción no se puede deshacer.",
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Nota de aprobación (opcional)',
            showCancelButton: true,
            confirmButtonText: 'Sí, Aprobar y Eliminar',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.assetService.approveSolicitud(id, result.value).subscribe({
                    next: () => {
                        Swal.fire('Aprobado', 'El activo ha sido eliminado.', 'success');
                        this.loadSolicitudes();
                    },
                    error: (err) => Swal.fire('Error', err.error.message || 'Error', 'error')
                });
            }
        });
    }

    rechazar(id: number) {
        Swal.fire({
            title: '¿Rechazar Solicitud?',
            text: "La solicitud será marcada como rechazada.",
            input: 'textarea',
            inputPlaceholder: 'Motivo del rechazo (Requerido)',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Rechazar',
            confirmButtonColor: '#f59e0b',
            preConfirm: (nota) => {
                if (!nota) Swal.showValidationMessage('Debes ingresar un motivo');
                return nota;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.assetService.rejectSolicitud(id, result.value).subscribe({
                    next: () => {
                        Swal.fire('Rechazado', 'La solicitud ha sido rechazada.', 'info');
                        this.loadSolicitudes();
                    },
                    error: (err) => Swal.fire('Error', err.error.message || 'Error', 'error')
                });
            }
        });
    }
}
