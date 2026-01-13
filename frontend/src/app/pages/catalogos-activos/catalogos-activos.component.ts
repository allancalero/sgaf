import { Component, OnInit, ChangeDetectorRef, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import {
    CatalogoActivosService,
    Proveedor,
    Clasificacion,
    Fuente,
    Tipo,
    Cheque
} from '../../services/catalogo-activos.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-catalogos-activos',
    standalone: true,
    imports: [CommonModule, FormsModule, MainLayoutComponent],
    templateUrl: './catalogos-activos.component.html'
})
export class CatalogosActivosComponent implements OnInit {
    activeTab = 'proveedores';
    showForm = false;
    activeFormTab = 'proveedor';
    loading = true;

    // Data
    proveedores: Proveedor[] = [];
    clasificaciones: Clasificacion[] = [];
    fuentes: Fuente[] = [];
    tipos: Tipo[] = [];
    cheques: Cheque[] = [];
    areas: any[] = [];
    usuarios: any[] = [];

    // Forms
    proveedorForm = { nombre: '', ruc: '', direccion: '', telefono: '', email: '' };
    editingProveedorId: number | null = null;

    clasificacionForm = { nombre: '', codigo: '', prefijo: '' };
    editingClasificacionId: number | null = null;

    fuenteForm = { nombre: '', estado: 'ACTIVO' };
    editingFuenteId: number | null = null;

    tipoForm = { nombre: '', clasificacion_id: null as number | null };
    editingTipoId: number | null = null;

    chequeForm = {
        numero_cheque: '',
        banco: '',
        cuenta_bancaria: '',
        monto_total: 0,
        fecha_emision: '',
        fecha_vencimiento: '',
        beneficiario: '',
        beneficiario_ruc: '',
        descripcion: '',
        estado: 'EMITIDO',
        area_solicitante_id: null as number | null,
        usuario_emisor_id: null as number | null
    };
    editingChequeId: number | null = null;

    constructor(
        private service: CatalogoActivosService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.loadData();
    }

    loadData() {
        this.loading = true;

        this.service.getProveedores().subscribe({
            next: (data) => { this.proveedores = data; this.checkLoading(); },
            error: () => this.checkLoading()
        });

        this.service.getClasificaciones().subscribe({
            next: (data) => { this.clasificaciones = data; this.checkLoading(); },
            error: () => this.checkLoading()
        });

        this.service.getFuentes().subscribe({
            next: (data) => { this.fuentes = data; this.checkLoading(); },
            error: () => this.checkLoading()
        });

        this.service.getTipos().subscribe({
            next: (data) => { this.tipos = data; this.checkLoading(); },
            error: () => this.checkLoading()
        });

        this.service.getCheques().subscribe({
            next: (data) => { this.cheques = data; this.checkLoading(); },
            error: () => this.checkLoading()
        });
    }

    checkLoading() {
        this.loading = false;
        this.cdr.detectChanges();
    }

    openForm(type: string) {
        this.activeFormTab = type;
        this.showForm = true;
        this.resetForms();
    }

    closeForm() {
        this.showForm = false;
        this.resetEditingIds();
        this.resetForms();
    }

    resetEditingIds() {
        this.editingProveedorId = null;
        this.editingClasificacionId = null;
        this.editingFuenteId = null;
        this.editingTipoId = null;
        this.editingChequeId = null;
    }

    resetForms() {
        if (!this.editingProveedorId) this.proveedorForm = { nombre: '', ruc: '', direccion: '', telefono: '', email: '' };
        if (!this.editingClasificacionId) this.clasificacionForm = { nombre: '', codigo: '', prefijo: '' };
        if (!this.editingFuenteId) this.fuenteForm = { nombre: '', estado: 'ACTIVO' };
        if (!this.editingTipoId) this.tipoForm = { nombre: '', clasificacion_id: null };
        if (!this.editingChequeId) this.chequeForm = {
            numero_cheque: '', banco: '', cuenta_bancaria: '', monto_total: 0,
            fecha_emision: '', fecha_vencimiento: '', beneficiario: '', beneficiario_ruc: '',
            descripcion: '', estado: 'EMITIDO', area_solicitante_id: null, usuario_emisor_id: null
        };
    }

    // ==================== PROVEEDORES ====================
    saveProveedor() {
        if (!this.proveedorForm.nombre) {
            Swal.fire('Atención', 'El nombre del proveedor es obligatorio', 'warning');
            return;
        }

        const action = this.editingProveedorId
            ? this.service.updateProveedor(this.editingProveedorId, this.proveedorForm)
            : this.service.createProveedor(this.proveedorForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingProveedorId ? 'Actualizado' : 'Creado',
                    text: `El proveedor se ha ${this.editingProveedorId ? 'actualizado' : 'creado'} correctamente.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadData();
            },
            error: (err) => {
                const msg = err.error?.message || err.message || 'Error desconocido';
                Swal.fire('Error', `No se pudo procesar el proveedor: ${msg}`, 'error');
            }
        });
    }

    startEditProveedor(item: Proveedor) {
        this.editingProveedorId = item.id;
        this.proveedorForm = {
            nombre: item.nombre,
            ruc: item.ruc || '',
            direccion: item.direccion || '',
            telefono: item.telefono || '',
            email: item.email || ''
        };
        this.openForm('proveedor');
    }

    deleteProveedor(id: number) {
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
                this.service.deleteProveedor(id).subscribe({
                    next: () => {
                        Swal.fire('Eliminado', 'El proveedor ha sido eliminado', 'success');
                        this.loadData();
                    },
                    error: () => Swal.fire('Error', 'No se pudo eliminar el proveedor', 'error')
                });
            }
        });
    }

    // ==================== CLASIFICACIONES ====================
    saveClasificacion() {
        if (!this.clasificacionForm.nombre) {
            Swal.fire('Atención', 'El nombre es obligatorio', 'warning');
            return;
        }

        const action = this.editingClasificacionId
            ? this.service.updateClasificacion(this.editingClasificacionId, this.clasificacionForm)
            : this.service.createClasificacion(this.clasificacionForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingClasificacionId ? 'Actualizado' : 'Creado',
                    text: `La clasificación se ha ${this.editingClasificacionId ? 'actualizado' : 'creado'} correctamente.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadData();
            },
            error: (err) => {
                const msg = err.error?.message || err.message || 'Error desconocido';
                Swal.fire('Error', `No se pudo procesar la clasificación: ${msg}`, 'error');
            }
        });
    }

    startEditClasificacion(item: Clasificacion) {
        this.editingClasificacionId = item.id;
        this.clasificacionForm = {
            nombre: item.nombre,
            codigo: item.codigo || '',
            prefijo: item.prefijo || ''
        };
        this.openForm('clasificacion');
    }

    deleteClasificacion(id: number) {
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
                this.service.deleteClasificacion(id).subscribe({
                    next: () => {
                        Swal.fire('Eliminado', 'La clasificación ha sido eliminada', 'success');
                        this.loadData();
                    },
                    error: () => Swal.fire('Error', 'No se pudo eliminar la clasificación', 'error')
                });
            }
        });
    }

    // ==================== FUENTES ====================
    saveFuente() {
        if (!this.fuenteForm.nombre) {
            Swal.fire('Atención', 'El nombre es obligatorio', 'warning');
            return;
        }

        const action = this.editingFuenteId
            ? this.service.updateFuente(this.editingFuenteId, this.fuenteForm)
            : this.service.createFuente(this.fuenteForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingFuenteId ? 'Actualizado' : 'Creado',
                    text: `La fuente se ha ${this.editingFuenteId ? 'actualizado' : 'creado'} correctamente.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadData();
            },
            error: (err) => {
                const msg = err.error?.message || err.message || 'Error desconocido';
                Swal.fire('Error', `No se pudo procesar la fuente: ${msg}`, 'error');
            }
        });
    }

    startEditFuente(item: Fuente) {
        this.editingFuenteId = item.id;
        this.fuenteForm = { nombre: item.nombre, estado: item.estado };
        this.openForm('fuente');
    }

    deleteFuente(id: number) {
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
                this.service.deleteFuente(id).subscribe({
                    next: () => {
                        Swal.fire('Eliminado', 'La fuente ha sido eliminada', 'success');
                        this.loadData();
                    },
                    error: () => Swal.fire('Error', 'No se pudo eliminar la fuente', 'error')
                });
            }
        });
    }

    // ==================== TIPOS ====================
    saveTipo() {
        if (!this.tipoForm.nombre || !this.tipoForm.clasificacion_id) {
            Swal.fire('Atención', 'El nombre y la clasificación son obligatorios', 'warning');
            return;
        }

        const action = this.editingTipoId
            ? this.service.updateTipo(this.editingTipoId, this.tipoForm)
            : this.service.createTipo(this.tipoForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingTipoId ? 'Actualizado' : 'Creado',
                    text: `El tipo se ha ${this.editingTipoId ? 'actualizado' : 'creado'} correctamente.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadData();
            },
            error: (err) => {
                const msg = err.error?.message || err.message || 'Error desconocido';
                Swal.fire('Error', `No se pudo procesar el tipo: ${msg}`, 'error');
            }
        });
    }

    startEditTipo(item: Tipo) {
        this.editingTipoId = item.id;
        this.tipoForm = { nombre: item.nombre, clasificacion_id: item.clasificacion_id };
        this.openForm('tipo');
    }

    deleteTipo(id: number) {
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
                this.service.deleteTipo(id).subscribe({
                    next: () => {
                        Swal.fire('Eliminado', 'El tipo ha sido eliminado', 'success');
                        this.loadData();
                    },
                    error: () => Swal.fire('Error', 'No se pudo eliminar el tipo', 'error')
                });
            }
        });
    }

    getClasificacionNombre(id: number | null | undefined): string {
        if (!id) return 'N/A';
        const clasificacion = this.clasificaciones.find(c => c.id === id);
        return clasificacion?.nombre || 'Desconocido';
    }

    // ==================== CHEQUES ====================
    saveCheque() {
        if (!this.chequeForm.numero_cheque || !this.chequeForm.banco || !this.chequeForm.beneficiario) {
            Swal.fire('Atención', 'Número de cheque, banco y beneficiario son obligatorios', 'warning');
            return;
        }

        const action = this.editingChequeId
            ? this.service.updateCheque(this.editingChequeId, this.chequeForm)
            : this.service.createCheque(this.chequeForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingChequeId ? 'Actualizado' : 'Creado',
                    text: `El cheque se ha ${this.editingChequeId ? 'actualizado' : 'creado'} correctamente.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadData();
            },
            error: (err) => {
                const msg = err.error?.message || err.message || 'Error desconocido';
                Swal.fire('Error', `No se pudo procesar el cheque: ${msg}`, 'error');
            }
        });
    }

    startEditCheque(item: Cheque) {
        this.editingChequeId = item.id;
        this.chequeForm = {
            numero_cheque: item.numero_cheque,
            banco: item.banco,
            cuenta_bancaria: item.cuenta_bancaria,
            monto_total: item.monto_total,
            fecha_emision: item.fecha_emision,
            fecha_vencimiento: item.fecha_vencimiento || '',
            beneficiario: item.beneficiario,
            beneficiario_ruc: item.beneficiario_ruc || '',
            descripcion: item.descripcion || '',
            estado: item.estado,
            area_solicitante_id: item.area_solicitante_id,
            usuario_emisor_id: item.usuario_emisor_id
        };
        this.openForm('cheque');
    }

    deleteCheque(id: number) {
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
                this.service.deleteCheque(id).subscribe({
                    next: () => {
                        Swal.fire('Eliminado', 'El cheque ha sido eliminado', 'success');
                        this.loadData();
                    },
                    error: () => Swal.fire('Error', 'No se pudo eliminar el cheque', 'error')
                });
            }
        });
    }

    // Keyboard Shortcuts
    @HostListener('window:keydown', ['$event'])
    handleKeyboardEvent(event: KeyboardEvent) {
        if (event.ctrlKey && event.altKey && event.key === 'n') {
            event.preventDefault();
            const map: { [key: string]: string } = {
                'proveedores': 'proveedor',
                'clasificaciones': 'clasificacion',
                'fuentes': 'fuente',
                'tipos': 'tipo',
                'cheques': 'cheque'
            };
            const formType = map[this.activeTab];
            if (formType) {
                this.openForm(formType);
            }
        }
    }
}
