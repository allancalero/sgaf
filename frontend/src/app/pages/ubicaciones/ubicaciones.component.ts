import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { UbicacionService, Area, Ubicacion } from '../../services/ubicacion.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-ubicaciones',
    standalone: true,
    imports: [CommonModule, FormsModule, MainLayoutComponent],
    templateUrl: './ubicaciones.component.html'
})
export class UbicacionesComponent implements OnInit {
    activeTab = 'areas'; // For the view/tables if needed, though we show both side by side
    showForm = false;
    activeFormTab = 'area'; // 'area' | 'ubicacion'

    areas: Area[] = [];
    ubicaciones: Ubicacion[] = [];
    loading = true;

    // Area form
    areaForm = { nombre: '', ubicacion_id: null as number | null, estado: 'ACTIVO' };
    editingAreaId: number | null = null;

    // Ubicacion form
    ubicacionForm = { nombre: '', direccion: '', estado: 'ACTIVO' };
    editingUbicacionId: number | null = null;

    constructor(
        private ubicacionService: UbicacionService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.loadData();
    }

    loadData() {
        this.loading = true;

        this.ubicacionService.getAreas().subscribe({
            next: (data) => {
                this.areas = data;
                this.checkLoading();
            },
            error: () => this.checkLoading()
        });

        this.ubicacionService.getUbicaciones().subscribe({
            next: (data) => {
                this.ubicaciones = data;
                this.checkLoading();
            }
        });
    }

    checkLoading() {
        // Simple mechanism to just turn off loading when data comes in
        this.loading = false;
        this.cdr.detectChanges();
    }

    openForm(type: 'area' | 'ubicacion') {
        this.activeFormTab = type;
        this.showForm = true;
        this.resetForms();
    }

    closeForm() {
        this.showForm = false;
        this.editingAreaId = null;
        this.editingUbicacionId = null;
        this.resetForms();
    }

    resetForms() {
        if (!this.editingAreaId) {
            this.areaForm = { nombre: '', ubicacion_id: null, estado: 'ACTIVO' };
        }
        if (!this.editingUbicacionId) {
            this.ubicacionForm = { nombre: '', direccion: '', estado: 'ACTIVO' };
        }
    }

    // AREA METHODS
    saveArea() {
        if (!this.areaForm.nombre) {
            Swal.fire('Atención', 'El nombre del área es obligatorio', 'warning');
            return;
        }

        const action = this.editingAreaId
            ? this.ubicacionService.updateArea(this.editingAreaId, this.areaForm)
            : this.ubicacionService.createArea(this.areaForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingAreaId ? 'Actualizado' : 'Creado',
                    text: `El área se ha ${this.editingAreaId ? 'actualizado' : 'creado'} correctamente.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadData();
            },
            error: (err) => {
                console.error('Error saving area:', err);
                // Extract error message if available from Laravel JSON response
                const msg = err.error?.message || err.message || 'Error desconocido del servidor';
                Swal.fire('Error', `No se pudo procesar el área: ${msg}`, 'error');
            }
        });
    }

    startEditArea(area: Area) {
        this.editingAreaId = area.id;
        this.areaForm = {
            nombre: area.nombre,
            ubicacion_id: area.ubicacion_id ?? null,
            estado: area.estado
        };
        this.openForm('area');
    }

    deleteArea(id: number) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede revertir',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                this.ubicacionService.deleteArea(id).subscribe({
                    next: () => {
                        Swal.fire('Eliminado', 'El área ha sido eliminada', 'success');
                        this.loadData();
                    },
                    error: (err) => Swal.fire('Error', 'No se pudo eliminar el área', 'error')
                });
            }
        });
    }

    // UBICACION METHODS
    saveUbicacion() {
        if (!this.ubicacionForm.nombre) {
            Swal.fire('Atención', 'El nombre de la ubicación es obligatorio', 'warning');
            return;
        }

        const action = this.editingUbicacionId
            ? this.ubicacionService.updateUbicacion(this.editingUbicacionId, this.ubicacionForm)
            : this.ubicacionService.createUbicacion(this.ubicacionForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingUbicacionId ? 'Actualizado' : 'Creado',
                    text: `La ubicación se ha ${this.editingUbicacionId ? 'actualizada' : 'creada'} correctamente.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadData();
            },
            error: (err) => {
                console.error('Error saving ubicacion:', err);
                const msg = err.error?.message || err.message || 'Error desconocido del servidor';
                Swal.fire('Error', `No se pudo procesar la ubicación: ${msg}`, 'error');
            }
        });
    }

    startEditUbicacion(ubicacion: Ubicacion) {
        this.editingUbicacionId = ubicacion.id;
        this.ubicacionForm = {
            nombre: ubicacion.nombre,
            direccion: ubicacion.direccion || '',
            estado: ubicacion.estado
        };
        this.openForm('ubicacion');
    }

    deleteUbicacion(id: number) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede revertir',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                this.ubicacionService.deleteUbicacion(id).subscribe({
                    next: () => {
                        Swal.fire('Eliminado', 'La ubicación ha sido eliminada', 'success');
                        this.loadData();
                    },
                    error: () => Swal.fire('Error', 'No se pudo eliminar la ubicación', 'error')
                });
            }
        });
    }

    getUbicacionNombre(id: number | null | undefined): string {
        if (!id) return 'General / Sin asginar';
        const ubicacion = this.ubicaciones.find(u => u.id === id);
        return ubicacion?.nombre || 'Desconocido';
    }
}
