import { Component, OnInit, ChangeDetectorRef, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { AssetService } from '../../services/asset.service';
import { UbicacionService } from '../../services/ubicacion.service';
import { PersonalService } from '../../services/personal.service';
import { DepreciacionService } from '../../services/depreciacion.service';
import { QrScannerComponent } from '../../components/qr-scanner/qr-scanner.component';
import { AuthService } from '../../services/auth.service';
import { Asset } from '../../models/asset.model';
import { Subject, forkJoin } from 'rxjs';
import { debounceTime, distinctUntilChanged, takeUntil } from 'rxjs/operators';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-assets',
    standalone: true,
    imports: [CommonModule, FormsModule, RouterModule, QrScannerComponent],
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
    private destroy$ = new Subject<void>();

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
        monto_cheque_utilizado: 0,
        vida_util_anos: null,
        valor_residual: 0,
        metodo_depreciacion: 'linea_recta',
        custom_fields: {}
    };
    depCatalog: any[] = [];
    showDepCatalog = false;
    editingId: number | null = null;

    currentPage = 1;
    lastPage = 1;
    pageSize = 15;
    totalAssets = 0;
    pageSizeOptions = [10, 15, 25, 50, 100];

    // Photo handling
    selectedFile: File | null = null;
    fotoPreview: string | null = null;

    // Permissions
    canCreate = false;
    canEdit = false;
    canDelete = false;

    constructor(
        private assetService: AssetService,
        private ubiService: UbicacionService,
        private personalService: PersonalService,
        private depreciacionService: DepreciacionService,
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

        // Initialize permissions
        this.authService.currentUser.subscribe(user => {
            if (user && user.permissions) {
                const permissions = user.permissions.map((p: any) => p.name);
                this.canCreate = permissions.includes('activos.create') || permissions.includes('activos.manage');
                this.canEdit = permissions.includes('activos.edit') || permissions.includes('activos.manage');
                this.canDelete = permissions.includes('activos.delete') || permissions.includes('activos.manage');
            }
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
        this.loadDepCatalog();
    }

    ngOnDestroy() {
        this.destroy$.next();
        this.destroy$.complete();
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
            next: (res: any) => {
                // Support both standard Paginator and ResourceCollection structures
                this.assets = res.data;

                // If wrapped in Resource (has meta)
                if (res.meta) {
                    this.currentPage = res.meta.current_page;
                    this.lastPage = res.meta.last_page;
                    this.totalAssets = res.meta.total;
                    this.pageSize = res.meta.per_page;
                } else {
                    // Fallback for standard Paginator (if any)
                    this.currentPage = res.current_page;
                    this.lastPage = res.last_page;
                    this.totalAssets = res.total;
                    this.pageSize = res.per_page || this.pageSize;
                }

                this.loading = false;
                this.cdr.detectChanges();
            },
            error: (err: any) => {
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
            // Find the area to get its linked ubicacion_id
            const selectedArea = this.areas.find(a => a.id == this.assetForm.area_id);
            if (selectedArea && selectedArea.ubicacion_id) {
                this.assetForm.ubicacion_id = selectedArea.ubicacion_id;
            }

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

    // Dynamic fields configuration based on accounting code prefix (e.g., "123-004-007")
    dynamicFieldConfigByCode: any = {
        // 123-004-007: Equipos de Computación
        '123-004-007': [
            { key: 'procesador', label: 'Procesador', type: 'text' },
            { key: 'memoria_ram', label: 'Memoria RAM', type: 'text' },
            { key: 'almacenamiento', label: 'Almacenamiento', type: 'text' },
            { key: 'sistema_operativo', label: 'Sistema Operativo', type: 'text' },
            { key: 'hostname', label: 'Hostname', type: 'text' },
            { key: 'direccion_mac', label: 'Dirección MAC', type: 'text' },
            { key: 'ip_asignada', label: 'IP Asignada', type: 'text' }
        ],
        // 123-004-004: Equipo de transporte, tracción y elevación
        '123-004-004': [
            { key: 'numero_placa', label: 'Número de Placa', type: 'text' },
            { key: 'numero_chasis_vin', label: 'Chasis / VIN', type: 'text' },
            { key: 'numero_motor', label: 'Número de Motor', type: 'text' },
            { key: 'anio_fabricacion', label: 'Año de Fabricación', type: 'number' },
            { key: 'color_especifico', label: 'Color Específico', type: 'text' },
            { key: 'tipo_combustible', label: 'Tipo de Combustible', type: 'select', options: ['GASOLINA', 'DIESEL', 'ELÉCTRICO', 'HÍBRIDO'] },
            { key: 'cilindraje', label: 'Cilindraje', type: 'text' },
            { key: 'kilometraje_actual', label: 'Kilometraje Actual', type: 'number' },
            { key: 'numero_poliza_seguro', label: 'Número de Póliza', type: 'text' }
        ],
        // 123-004-001: Equipos de Oficina / Mobiliario
        '123-004-001': [
            { key: 'material_principal', label: 'Material Principal', type: 'text' },
            { key: 'dimensiones', label: 'Dimensiones (Al x An x Fo)', type: 'text' },
            { key: 'tipo_estructura', label: 'Tipo de Estructura', type: 'text' },
            { key: 'numero_gavetas', label: 'Número de Gavetas', type: 'number' }
        ],
        // 123-004-002: Equipos médicos, Sanitarios y de laboratorio
        '123-004-002': [
            { key: 'precision', label: 'Precisión', type: 'text' },
            { key: 'capacidad', label: 'Capacidad', type: 'text' },
            { key: 'fecha_ultima_calibracion', label: 'Fecha Última Calibración', type: 'date' },
            { key: 'tipo_medicion', label: 'Tipo de Medición', type: 'text' }
        ],
        // 123-004-003: Equipo educacional y recreativo
        '123-004-003': [
            { key: 'tipo_actividad', label: 'Tipo de Actividad', type: 'text' },
            { key: 'capacidad_personas', label: 'Capacidad de Personas', type: 'number' },
            { key: 'material_principal', label: 'Material Principal', type: 'text' }
        ],
        // 123-004-005: Equipos de producción
        '123-004-005': [
            { key: 'capacidad_produccion', label: 'Capacidad de Producción', type: 'text' },
            { key: 'potencia', label: 'Potencia', type: 'text' },
            { key: 'voltaje', label: 'Voltaje', type: 'select', options: ['110v', '220v', '380v'] },
            { key: 'horometro_horas_uso', label: 'Horómetro (Horas)', type: 'number' }
        ],
        // 123-004-006: Equipos de comunicaciones y señalización
        '123-004-006': [
            { key: 'tipo_dispositivo', label: 'Tipo de Dispositivo', type: 'text' },
            { key: 'frecuencia_banda', label: 'Frecuencia / Banda', type: 'text' },
            { key: 'alcance', label: 'Alcance (km)', type: 'text' },
            { key: 'numero_serie_imei', label: 'IMEI / Serial', type: 'text' }
        ],
        // 123-004-008: Herramientas mayores
        '123-004-008': [
            { key: 'fuente_energia', label: 'Fuente de Energía', type: 'select', options: ['ELÉCTRICA', 'GASOLINA', 'MANUAL', 'BATERÍA'] },
            { key: 'potencia', label: 'Potencia', type: 'text' },
            { key: 'voltaje', label: 'Voltaje', type: 'select', options: ['110v', '220v', 'N/A'] },
            { key: 'uso_previsto', label: 'Uso Previsto', type: 'text' }
        ],
        // 123-004-009: Equipos de Seguridad y militar
        '123-004-009': [
            { key: 'tipo_dispositivo', label: 'Tipo de Dispositivo', type: 'text' },
            { key: 'es_arma_fuego', label: 'Es Arma de Fuego', type: 'boolean' },
            { key: 'calibre', label: 'Calibre (si aplica)', type: 'text' },
            { key: 'registro_balistica', label: 'Registro Balística', type: 'text' }
        ],
        // 123-002-008: Bienes de Defensa
        '123-002-008': [
            { key: 'tipo_dispositivo', label: 'Tipo de Dispositivo', type: 'text' },
            { key: 'es_arma_fuego', label: 'Es Arma de Fuego', type: 'boolean' },
            { key: 'calibre', label: 'Calibre', type: 'text' },
            { key: 'registro_balistica', label: 'Registro Balística', type: 'text' }
        ],
        // 123-002-009: Bienes de Seguridad
        '123-002-009': [
            { key: 'tipo_dispositivo', label: 'Tipo de Dispositivo', type: 'text' },
            { key: 'capacidad', label: 'Capacidad', type: 'text' }
        ]
    };

    get currentDynamicFields(): any[] {
        const id = this.assetForm.clasificacion_id;
        if (!id) return [];

        // Find the selected classification to get its accounting code
        const selectedClasificacion = this.clasificaciones.find((c: any) => c.id == id);
        if (!selectedClasificacion || !selectedClasificacion.codigo) return [];

        // Extract the first 3 segments of the code (e.g., "123-004-007" from "123-004-007-000-000-000")
        const codigo = selectedClasificacion.codigo.replace(/ /g, '-'); // Normalize spaces to dashes
        const segments = codigo.split('-').slice(0, 3).join('-');

        return this.dynamicFieldConfigByCode[segments] || [];
    }

    loadDepCatalog() {
        this.depreciacionService.getCatalogo().subscribe(data => {
            this.depCatalog = data;
            this.cdr.detectChanges();
        });
    }

    applyDepCatalog(cat: any) {
        this.assetForm.vida_util_anos = cat.vida_util_anos;
        this.showDepCatalog = false;
        this.cdr.detectChanges();
    }

    openCreateForm() {
        this.editingId = null;
        const initialAreaId = this.areas[0]?.id || null;
        const initialUbiId = this.areas[0]?.ubicacion_id || this.ubicaciones[0]?.id || null;

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
            area_id: initialAreaId,
            ubicacion_id: initialUbiId,
            clasificacion_id: this.clasificaciones[0]?.id || null,
            tipo_activo_id: null,
            fuente_financiamiento_id: this.fuentes[0]?.id || null,
            proveedor_id: null,
            personal_id: null,
            cheque_id: null,
            monto_cheque_utilizado: 0,
            vida_util_anos: null,
            valor_residual: 0,
            metodo_depreciacion: 'linea_recta',
            custom_fields: {}
        };
        this.showForm = true;

        // Initialize form personal list based on default area
        if (this.assetForm.area_id) {
            this.loadFormPersonal(this.assetForm.area_id);
        } else {
            this.formPersonal = [];
        }

        // Trigger auto-code generation for initial classification
        this.onClasificacionChange();
    }

    onClasificacionChange() {
        if (this.assetForm.clasificacion_id && !this.editingId) {
            // Reset dynamic fields when category changes and we are not editing
            this.assetForm.custom_fields = {};

            this.assetService.getNextCode(this.assetForm.clasificacion_id).subscribe({
                next: (res) => {
                    if (res.code) {
                        this.assetForm.codigo_inventario = res.code;
                    }
                },
                error: (err) => {
                    console.error('Error auto-generating code', err);
                }
            });
        }
    }

    startEdit(asset: Asset) {
        this.editingId = asset.id;
        this.assetService.getAsset(asset.id).subscribe(data => {
            this.assetForm = { ...data };
            if (!this.assetForm.custom_fields || Array.isArray(this.assetForm.custom_fields)) {
                this.assetForm.custom_fields = {};
            }
            this.showForm = true;
            this.cdr.detectChanges();
        });
    }

    closeForm() {
        this.showForm = false;
        this.editingId = null;
        this.selectedFile = null;
        this.fotoPreview = null;
    }

    // Photo handling methods
    onFileSelected(event: any) {
        const file = event.target.files[0];
        if (file) {
            this.selectedFile = file;
            const reader = new FileReader();
            reader.onload = (e: any) => {
                this.fotoPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    removeFoto() {
        this.selectedFile = null;
        this.fotoPreview = null;
        this.assetForm.foto = null;
    }

    getFullFotoUrl(foto: string): string {
        if (!foto) return '';
        if (foto.startsWith('http')) return foto;
        return `http://localhost:8000/storage/${foto}`;
    }

    saveAsset() {
        // Build FormData if there's a file to upload
        let data: any = this.assetForm;
        if (this.selectedFile) {
            const formData = new FormData();
            // Append all form fields
            Object.keys(this.assetForm).forEach(key => {
                const value = this.assetForm[key];
                if (value !== null && value !== undefined && value !== '') {
                    if (key === 'custom_fields' && typeof value === 'object') {
                        formData.append(key, JSON.stringify(value));
                    } else {
                        formData.append(key, value);
                    }
                }
            });
            // Append the image file
            formData.append('foto', this.selectedFile, this.selectedFile.name);
            data = formData;
        }

        const action = this.editingId
            ? this.assetService.updateAsset(this.editingId, data)
            : this.assetService.createAsset(data);

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
        if (!this.canDelete) {
            // Request Deletion Flow
            Swal.fire({
                title: 'Solicitar Baja',
                text: "No tienes permisos para eliminar directamente. ¿Deseas enviar una solicitud de baja al administrador?",
                icon: 'question',
                input: 'textarea',
                inputPlaceholder: 'Escribe el motivo de la baja...',
                inputAttributes: {
                    'aria-label': 'Escribe el motivo de la baja'
                },
                showCancelButton: true,
                confirmButtonText: 'Enviar Solicitud',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3b82f6',
                preConfirm: (motivo) => {
                    if (!motivo) {
                        Swal.showValidationMessage('Debes ingresar un motivo');
                    }
                    return motivo;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.assetService.createSolicitud(id, result.value).subscribe({
                        next: () => {
                            Swal.fire('Solicitud Enviada', 'El administrador revisará tu solicitud.', 'success');
                        },
                        error: (err) => {
                            Swal.fire('Error', err.error.message || 'Error al enviar solicitud', 'error');
                        }
                    });
                }
            });
            return;
        }

        // Admin Deletion Flow
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

    // Quick View Modal
    showQuickViewModal = false;
    quickViewAsset: any = null;
    quickViewQrUrl: string | null = null;

    openQuickView(asset: any) {
        this.quickViewAsset = asset;
        this.quickViewQrUrl = null;
        this.showQuickViewModal = true;

        // Load QR code
        this.assetService.getQrBlob(asset.id).subscribe({
            next: (blob) => {
                this.quickViewQrUrl = URL.createObjectURL(blob);
                this.cdr.detectChanges();
            },
            error: (err) => console.error('Error loading QR', err)
        });
    }

    closeQuickView() {
        this.showQuickViewModal = false;
        this.quickViewAsset = null;
        if (this.quickViewQrUrl) {
            URL.revokeObjectURL(this.quickViewQrUrl);
            this.quickViewQrUrl = null;
        }
    }

    imprimirActa(id: number) {
        this.loading = true;
        this.assetService.downloadActa(id).subscribe({
            next: (blob) => {
                this.loading = false;
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank');
                setTimeout(() => window.URL.revokeObjectURL(url), 10000);
            },
            error: (err) => {
                this.loading = false;
                console.error('Error downloading PDF', err);
                Swal.fire('Error', 'No se pudo generar el acta.', 'error');
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
