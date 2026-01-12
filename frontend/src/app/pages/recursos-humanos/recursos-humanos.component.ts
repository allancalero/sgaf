import { Component, OnInit, ChangeDetectorRef, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { PersonalService, Personal, Cargo } from '../../services/personal.service';
import { UbicacionService, Area, Ubicacion } from '../../services/ubicacion.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-recursos-humanos',
    standalone: true,
    imports: [CommonModule, FormsModule, MainLayoutComponent],
    templateUrl: './recursos-humanos.component.html'
})
export class RecursosHumanosComponent implements OnInit {
    activeTab = 'personal';
    showForm = false;
    activeFormTab = 'personal'; // 'personal' | 'cargo'

    personal: Personal[] = [];
    cargos: Cargo[] = [];
    allCargos: Cargo[] = [];
    areas: Area[] = [];
    ubicaciones: Ubicacion[] = [];
    loading = true;
    totalPersonal = 0;
    totalCargos = 0;
    currentPage = 1;
    lastPage = 1;
    pageSize = 10;
    pageSizeOptions = [10, 25, 50, 100];
    search = '';

    // Cargo Pagination
    cargoCurrentPage = 1;
    cargoLastPage = 1;
    cargoPageSize = 10;

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
        this.loadStaticDependencies();
    }

    loadPersonal() {
        this.loading = true;
        this.personalService.getPersonal(this.currentPage, this.search, '', this.pageSize).subscribe({
            next: (res) => {
                this.personal = res.data;
                this.totalPersonal = res.total;
                this.lastPage = res.last_page;
                this.pageSize = (res as any).per_page || this.pageSize;
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
        this.personalService.getCargos(this.cargoCurrentPage, this.cargoPageSize).subscribe({
            next: (res) => {
                this.cargos = res.data;
                this.totalCargos = res.total;
                this.cargoLastPage = res.last_page;
                this.cargoPageSize = (res as any).per_page || this.cargoPageSize;
                this.cdr.detectChanges();
            }
        });
    }

    loadStaticDependencies() {

        this.personalService.getAllCargos().subscribe(data => {
            this.allCargos = data;
        });

        this.ubicacionService.getAllAreas().subscribe(data => {
            this.areas = data;
        });

        this.ubicacionService.getAllUbicaciones().subscribe(data => {
            this.ubicaciones = data;
        });
    }

    onSearch(event: any) {
        this.search = event.target.value;
        this.currentPage = 1;
        this.loadPersonal();
    }

    onPageSizeChange() {
        this.currentPage = 1;
        this.loadPersonal();
    }

    onCargoPageSizeChange() {
        this.cargoCurrentPage = 1;
        this.loadCargos();
    }

    changePage(page: number) {
        if (page >= 1 && page <= this.lastPage) {
            this.currentPage = page;
            this.loadPersonal();
        }
    }

    changeCargoPage(page: number) {
        if (page >= 1 && page <= this.cargoLastPage) {
            this.cargoCurrentPage = page;
            this.loadCargos();
        }
    }

    // FORM VISIBILITY
    openForm(type: 'personal' | 'cargo') {
        this.activeFormTab = type;
        this.showForm = true;
        this.resetForms();
    }

    closeForm() {
        this.showForm = false;
        this.editingPersonalId = null;
        this.editingCargoId = null;
        this.resetForms();
    }

    resetForms() {
        if (!this.editingPersonalId) {
            this.personalForm = { nombre: '', apellido: '', cargo_id: undefined, area_id: undefined, estado: 'ACTIVO', numero_empleado: '' };
        }
        this.selectedFile = null;
        this.imagePreview = null;
        if (!this.editingCargoId) {
            this.cargoForm = { nombre: '', estado: 'ACTIVO' };
        }
    }

    selectedFile: File | null = null;
    imagePreview: string | ArrayBuffer | null = null;

    // PERSONAL ACTIONS
    onFileSelected(event: any) {
        const file = event.target.files[0];
        if (file) {
            this.selectedFile = file;
            const reader = new FileReader();
            reader.onload = e => this.imagePreview = reader.result;
            reader.readAsDataURL(file);
        }
    }

    savePersonal() {
        // Construct FormData
        const formData = new FormData();
        Object.keys(this.personalForm).forEach(key => {
            const value = (this.personalForm as any)[key];
            if (value !== undefined && value !== null) {
                formData.append(key, value);
            }
        });

        if (this.selectedFile) {
            formData.append('foto', this.selectedFile);
        }

        const action = this.editingPersonalId
            ? this.personalService.updatePersonal(this.editingPersonalId, formData)
            : this.personalService.createPersonal(formData);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingPersonalId ? 'Actualizado' : 'Creado',
                    text: 'El registro de personal ha sido procesado.',
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadData();
            },
            error: () => Swal.fire('Error', 'No se pudo procesar la solicitud', 'error')
        });
    }

    startEditPersonal(p: Personal) {
        this.editingPersonalId = p.id;
        this.personalForm = { ...p };
        if (p.foto) {
            this.imagePreview = p.foto;
        }
        this.openForm('personal');
    }

    deletePersonal(id: number) {
        Swal.fire({
            title: '¿Estás seguro?', text: 'Esta acción no se puede revertir', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                this.personalService.deletePersonal(id).subscribe(() => {
                    Swal.fire('Eliminado', 'Personal eliminado', 'success');
                    this.loadData();
                });
            }
        });
    }

    // CARGO ACTIONS
    saveCargo() {
        const action = this.editingCargoId
            ? this.personalService.updateCargo(this.editingCargoId, this.cargoForm)
            : this.personalService.createCargo(this.cargoForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingCargoId ? 'Actualizado' : 'Creado',
                    text: 'El cargo ha sido procesado.',
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadData();
            },
            error: () => Swal.fire('Error', 'No se pudo procesar la solicitud', 'error')
        });
    }

    startEditCargo(c: Cargo) {
        this.editingCargoId = c.id;
        this.cargoForm = { ...c };
        this.openForm('cargo');
    }

    deleteCargo(id: number) {
        Swal.fire({
            title: '¿Estás seguro?', text: 'Esta acción no se puede revertir', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                this.personalService.deleteCargo(id).subscribe(() => {
                    Swal.fire('Eliminado', 'Cargo eliminado', 'success');
                    this.loadData();
                });
            }
        });
    }

    // Keyboard Shortcuts
    @HostListener('window:keydown', ['$event'])
    handleKeyboardEvent(event: KeyboardEvent) {
        if (event.ctrlKey && event.altKey && event.key === 'n') {
            event.preventDefault();
            this.openForm(this.activeTab === 'personal' ? 'personal' : 'cargo');
        }
    }
}
