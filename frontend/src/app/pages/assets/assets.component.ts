import { Component, OnInit, ChangeDetectorRef, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { AssetService } from '../../services/asset.service';
import { UbicacionService } from '../../services/ubicacion.service';
import { PersonalService } from '../../services/personal.service';
import { QrScannerComponent } from '../../components/qr-scanner/qr-scanner.component';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { AuthService } from '../../services/auth.service';
import { Asset } from '../../models/asset.model';
import { Subject, forkJoin } from 'rxjs';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-assets',
    standalone: true,
    imports: [CommonModule, FormsModule, RouterModule, QrScannerComponent, MainLayoutComponent],
    templateUrl: './assets.component.html',
    styleUrls: ['./assets.component.css']
})
export class AssetsComponent implements OnInit {
    assets: Asset[] = [];
    loading = true;
    showScanner = false;
    showForm = false; // Changed from showModal to showForm for side-panel style
    searchTerm = '';
    selectedAreaId: any = '';
    selectedPersonalId: any = '';
    selectedClasificacionId: any = '';
    selectedEstado: string = '';
    isMisActivos = false;
    searchUpdate = new Subject<string>();

    // Dependencies
    areas: any[] = [];
    ubicaciones: any[] = [];
    clasificaciones: any[] = [];
    fuentes: any[] = [];
    tipos: any[] = [];
    proveedores: any[] = [];
    cheques: any[] = [];
    personal: any[] = [];

    // Form
    assetForm: any = {
        codigo_inventario: '',
        nombre_activo: '',
        marca: '',
        modelo: '',
        serie: '',
        color: '',
        descripcion: '',
        cantidad: 1,
        precio_unitario: 0,
        precio_adquisicion: 0,
        fecha_adquisicion: '',
        numero_factura: '',
        estado: 'BUENO',
        area_id: null,
        ubicacion_id: null,
        clasificacion_id: null,
        tipo_activo_id: null,
        fuente_financiamiento_id: null,
        proveedor_id: null,
        personal_id: null,
        cheque_id: null,
        monto_cheque_utilizado: 0
    };
    editingId: number | null = null;

    currentPage = 1;
    lastPage = 1;
    pageSize = 15;
    totalAssets = 0;
    pageSizeOptions = [10, 15, 25, 50, 100];

    constructor(
        private assetService: AssetService,
        private ubiService: UbicacionService,
        private personalService: PersonalService,
        private authService: AuthService,
        private route: ActivatedRoute,
        private cdr: ChangeDetectorRef
    ) {
        this.searchUpdate.pipe(
            debounceTime(400),
            distinctUntilChanged()
        ).subscribe(value => {
            this.searchTerm = value;
            this.currentPage = 1;
            this.loadAssets();
        });
    }

    ngOnInit() {
        this.route.url.subscribe(url => {
            this.isMisActivos = url.some(segment => segment.path === 'mis-activos');
            if (this.isMisActivos) {
                this.authService.currentUser.subscribe(user => {
                    if (user && user.personal_id) {
                        this.selectedPersonalId = user.personal_id;
                    }
                    this.loadAssets();
                });
            } else {
                this.loadAssets();
            }
        });
        this.loadDependencies();
    }

    loadDependencies() {
        forkJoin({
            areas: this.ubiService.getAllAreas(),
            ubicaciones: this.ubiService.getAllUbicaciones(),
            clasificaciones: this.assetService.getClasificaciones(),
            fuentes: this.assetService.getFuentes(),
            tipos: this.assetService.getTipos(),
            proveedores: this.assetService.getProveedores(),
            cheques: this.assetService.getCheques(),
            personal: this.personalService.getAllPersonal()
        }).subscribe({
            next: (data) => {
                this.areas = data.areas;
                this.ubicaciones = data.ubicaciones;
                this.clasificaciones = data.clasificaciones;
                this.fuentes = data.fuentes;
                this.tipos = data.tipos;
                this.proveedores = data.proveedores;
                this.cheques = data.cheques;
                this.personal = data.personal;
                this.cdr.detectChanges();
            }
        });
    }

    loadAssets() {
        this.loading = true;
        this.assetService.getAssets(
            this.currentPage,
            this.searchTerm,
            this.selectedEstado,
            this.pageSize,
            this.selectedAreaId,
            this.selectedPersonalId,
            this.selectedClasificacionId
        ).subscribe({
            next: (res) => {
                this.assets = res.data;
                this.currentPage = res.current_page;
                this.lastPage = res.last_page;
                this.totalAssets = res.total;
                // Ensure pageSize is synced with response if available, or keep current
                this.pageSize = (res as any).per_page || this.pageSize;
                this.loading = false;
                this.cdr.detectChanges();
            },
            error: (err) => {
                console.error('Error loading assets:', err);
                this.loading = false;
                this.cdr.detectChanges();
            }
        });
    }

    onPageSizeChange() {
        this.currentPage = 1;
        this.loadAssets();
    }

    onAreaChange() {
        if (!this.isMisActivos) {
            this.selectedPersonalId = '';
            this.loadFilteredPersonal();
        }
        this.onFilterChange();
    }

    loadFilteredPersonal() {
        this.personalService.getAllPersonal(this.selectedAreaId).subscribe(data => {
            this.personal = data;
            this.cdr.detectChanges();
        });
    }

    // Form specific logic
    formPersonal: any[] = [];

    onFormAreaChange() {
        if (this.assetForm.area_id) {
            this.assetForm.personal_id = null; // Clear selection
            this.loadFormPersonal(this.assetForm.area_id);
        } else {
            this.formPersonal = [];
        }
    }

    loadFormPersonal(areaId: number) {
        this.personalService.getAllPersonal(areaId).subscribe(data => {
            this.formPersonal = data;
            this.cdr.detectChanges();
        });
    }

    onFilterChange() {
        this.currentPage = 1;
        this.loadAssets();
    }

    resetFilters() {
        this.searchTerm = '';
        this.selectedAreaId = '';
        if (!this.isMisActivos) {
            this.selectedPersonalId = '';
        }
        this.selectedClasificacionId = '';
        this.selectedEstado = '';
        this.currentPage = 1;
        this.loadAssets();
    }

    onSearch(event: any) {
        this.searchUpdate.next(event.target.value);
    }

    changePage(page: number) {
        if (page >= 1 && page <= this.lastPage) {
            this.currentPage = page;
            this.loadAssets();
        }
    }

    toggleScanner() {
        this.showScanner = !this.showScanner;
    }

    handleScanSuccess(code: string) {
        this.showScanner = false;
        this.searchTerm = code;
        this.currentPage = 1;
        this.loadAssets();
    }

    openCreateForm() {
        this.editingId = null;
        this.assetForm = {
            codigo_inventario: '',
            nombre_activo: '',
            marca: '',
            modelo: '',
            serie: '',
            color: '',
            descripcion: '',
            cantidad: 1,
            precio_unitario: 0,
            precio_adquisicion: 0,
            fecha_adquisicion: new Date().toISOString().split('T')[0],
            numero_factura: '',
            estado: 'BUENO',
            area_id: this.areas[0]?.id || null,
            ubicacion_id: this.ubicaciones[0]?.id || null,
            clasificacion_id: this.clasificaciones[0]?.id || null,
            tipo_activo_id: null,
            fuente_financiamiento_id: this.fuentes[0]?.id || null,
            proveedor_id: null,
            personal_id: null,
            cheque_id: null,
            monto_cheque_utilizado: 0
        };
        this.showForm = true;

        // Initialize form personal list based on default area
        if (this.assetForm.area_id) {
            this.loadFormPersonal(this.assetForm.area_id);
        } else {
            this.formPersonal = [];
        }
    }

    editAsset(asset: Asset) {
        this.editingId = asset.id;
        this.assetService.getAsset(asset.id).subscribe(data => {
            this.assetForm = { ...data };
            this.showForm = true;
            this.cdr.detectChanges();
        });
    }

    closeForm() {
        this.showForm = false;
        this.editingId = null;
    }

    saveAsset() {
        const action = this.editingId
            ? this.assetService.updateAsset(this.editingId, this.assetForm)
            : this.assetService.createAsset(this.assetForm);

        action.subscribe({
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: this.editingId ? '¡Actualizado!' : '¡Creado!',
                    text: `El activo se ha ${this.editingId ? 'actualizado' : 'creado'} correctamente.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                this.closeForm();
                this.loadAssets();
            },
            error: (err) => {
                Swal.fire('Error', err.error.message || 'Ocurrió un error al procesar la solicitud', 'error');
            }
        });
    }

    deleteAsset(id: number) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.assetService.deleteAsset(id).subscribe(() => {
                    Swal.fire('¡Eliminado!', 'El activo ha sido borrado.', 'success');
                    this.loadAssets();
                });
            }
        });
    }

    // Keyboard Shortcuts
    @HostListener('window:keydown', ['$event'])
    handleKeyboardEvent(event: KeyboardEvent) {
        if (event.ctrlKey && event.altKey && event.key === 'n') {
            event.preventDefault();
            this.openCreateForm();
        }
    }
}
