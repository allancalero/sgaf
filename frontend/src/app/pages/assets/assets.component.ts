import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { AssetService } from '../../services/asset.service';
import { UbicacionService } from '../../services/ubicacion.service';
import { PersonalService } from '../../services/personal.service';
import { QrScannerComponent } from '../../components/qr-scanner/qr-scanner.component';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
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

    // Pagination
    currentPage = 1;
    lastPage = 1;
    totalAssets = 0;

    constructor(
        private assetService: AssetService,
        private ubiService: UbicacionService,
        private personalService: PersonalService,
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
        this.loadAssets();
        this.loadDependencies();
    }

    loadDependencies() {
        forkJoin({
            areas: this.ubiService.getAreas(),
            ubicaciones: this.ubiService.getUbicaciones(),
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
        this.assetService.getAssets(this.currentPage, this.searchTerm).subscribe({
            next: (res) => {
                this.assets = res.data;
                this.currentPage = res.current_page;
                this.lastPage = res.last_page;
                this.totalAssets = res.total;
                this.loading = false;
                this.cdr.detectChanges();
            },
            error: (err) => {
                this.loading = false;
                this.cdr.detectChanges();
            }
        });
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
}
