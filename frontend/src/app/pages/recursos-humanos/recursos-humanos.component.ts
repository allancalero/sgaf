import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PersonalService, Personal, Cargo } from '../../services/personal.service';
import { UbicacionService, Area, Ubicacion } from '../../services/ubicacion.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-recursos-humanos',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './recursos-humanos.component.html'
})
export class RecursosHumanosComponent implements OnInit {
    activeTab: 'personal' | 'cargos' = 'personal';
    showForm = false;
    activeFormTab: 'personal' | 'cargo' = 'personal';

    personal: Personal[] = [];
    cargos: Cargo[] = [];
    allCargos: Cargo[] = [];
    areas: Area[] = [];
    ubicaciones: Ubicacion[] = [];
    loading = true;
    searchText = '';

    imagePreview: string | ArrayBuffer | null = null;
    selectedFile: File | null = null;

    // Personal Form
    personalForm: Partial<Personal> = {
        nombre: '',
        apellido: '',
        cargo_id: undefined,
        area_id: undefined,
        estado: 'ACTIVO',
        numero_empleado: '',
    };
    editingPersonalId: number | null = null;

    // Cargo Form
    cargoForm = { nombre: '', estado: 'ACTIVO' };
    editingCargoId: number | null = null;

    constructor(
        private personalService: PersonalService,
        private ubicacionService: UbicacionService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.loadData();
    }

    loadData() {
        this.loadPersonal();
        this.loadCargos();
        this.loadStaticData();
    }

    loadStaticData() {
        this.ubicacionService.getAllAreas().subscribe(data => {
            this.areas = data;
        });

        this.ubicacionService.getAllUbicaciones().subscribe(data => {
            this.ubicaciones = data;
        });
    }

    loadPersonal() {
        this.loading = true;
        this.personalService.getAllPersonal().subscribe({
            next: (res) => {
                this.personal = res;
                this.loading = false;
                this.cdr.detectChanges();
            },
            error: () => {
                this.loading = false;
                this.cdr.detectChanges();
            }
        });
    }

    loadCargos() {
        this.personalService.getAllCargos().subscribe({
            next: (res) => {
                this.cargos = res;
                this.allCargos = res;
                this.cdr.detectChanges();
            }
        });
    }

    get filteredPersonal(): Personal[] {
        if (!this.searchText) return this.personal;
        const search = this.searchText.toLowerCase();
        return this.personal.filter(p =>
            p.nombre.toLowerCase().includes(search) ||
            p.apellido.toLowerCase().includes(search) ||
            p.cargo?.toLowerCase().includes(search) ||
            p.area?.toLowerCase().includes(search) ||
            p.numero_empleado?.toLowerCase().includes(search)
        );
    }

    get filteredCargos(): Cargo[] {
        if (!this.searchText) return this.cargos;
        const search = this.searchText.toLowerCase();
        return this.cargos.filter(c =>
            c.nombre.toLowerCase().includes(search)
        );
    }

    onFileSelected(event: any) {
        const file = event.target.files[0];
        if (file) {
            this.selectedFile = file;
            const reader = new FileReader();
            reader.onload = () => {
                this.imagePreview = reader.result;
                this.cdr.detectChanges();
            };
            reader.readAsDataURL(file);
        }
    }

    // FORM VISIBILITY
    openForm(type: 'personal' | 'cargo') {
        this.showForm = true;
        this.activeFormTab = type;
        this.resetForms();
    }

    closeForm() {
        this.showForm = false;
        this.editingPersonalId = null;
        this.editingCargoId = null;
        this.imagePreview = null;
        this.selectedFile = null;
        this.resetForms();
    }

    resetForms() {
        if (!this.editingPersonalId) {
            this.personalForm = {
                nombre: '',
                apellido: '',
                cargo_id: undefined,
                area_id: undefined,
                estado: 'ACTIVO',
                numero_empleado: '',
            };
        }
        if (!this.editingCargoId) {
            this.cargoForm = { nombre: '', estado: 'ACTIVO' };
        }
    }

    // PERSONAL ACTIONS
    savePersonal() {
        if (!this.personalForm.nombre || !this.personalForm.apellido) {
            Swal.fire('Atención', 'Nombre y apellido son obligatorios', 'warning');
            return;
        }

        const action = this.editingPersonalId
            ? this.personalService.updatePersonal(this.editingPersonalId, this.personalForm)
            : this.personalService.createPersonal(this.personalForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingPersonalId ? 'Actualizado' : 'Registrado',
                    text: `El personal ha sido ${this.editingPersonalId ? 'actualizado' : 'registrado'} con éxito.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadPersonal();
            },
            error: (err) => {
                const msg = err.error?.message || 'Error al procesar la solicitud';
                Swal.fire('Error', msg, 'error');
            }
        });
    }

    startEditPersonal(p: Personal) {
        this.editingPersonalId = p.id;
        this.personalForm = { ...p };
        this.openForm('personal');
    }

    deletePersonal(id: number) {
        Swal.fire({
            title: '¿Eliminar registro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                this.personalService.deletePersonal(id).subscribe({
                    next: () => {
                        Swal.fire('Eliminado', 'El registro ha sido eliminado.', 'success');
                        this.loadPersonal();
                    }
                });
            }
        });
    }

    // CARGO ACTIONS
    saveCargo() {
        if (!this.cargoForm.nombre) {
            Swal.fire('Atención', 'El nombre del cargo es obligatorio', 'warning');
            return;
        }

        const action = this.editingCargoId
            ? this.personalService.updateCargo(this.editingCargoId, this.cargoForm)
            : this.personalService.createCargo(this.cargoForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingCargoId ? 'Actualizado' : 'Registrado',
                    text: `El cargo ha sido ${this.editingCargoId ? 'actualizado' : 'registrado'} con éxito.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadCargos();
            }
        });
    }

    startEditCargo(c: Cargo) {
        this.editingCargoId = c.id;
        this.cargoForm = { nombre: c.nombre, estado: c.estado };
        this.openForm('cargo');
    }

    deleteCargo(id: number) {
        Swal.fire({
            title: '¿Eliminar cargo?',
            text: 'Asegúrese de que no haya personal asignado a este cargo',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                this.personalService.deleteCargo(id).subscribe({
                    next: () => {
                        Swal.fire('Eliminado', 'El cargo ha sido eliminado.', 'success');
                        this.loadCargos();
                    },
                    error: (err) => {
                        const msg = err.error?.message || 'No se pudo eliminar el cargo';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    }
}
