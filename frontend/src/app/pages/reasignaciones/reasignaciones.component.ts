import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ReasignacionService } from '../../services/reasignacion.service';
import { PersonalService, Personal } from '../../services/personal.service';
import { AssetService } from '../../services/asset.service';
import { UbicacionService } from '../../services/ubicacion.service';
import { Asset } from '../../models/asset.model';
import { environment } from '../../../environments/environment';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-reasignaciones',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './reasignaciones.component.html'
})
export class ReasignacionesComponent implements OnInit {
    reasignaciones: any[] = [];
    personal: Personal[] = [];
    assets: Asset[] = [];
    clasificaciones: any[] = [];
    areas: any[] = [];
    loading = true;
    showForm = false;
    showDetails = false;
    selectedReasignacion: any = null;
    storageUrl = environment.apiUrl.replace('/api', '') + '/storage/';

    // Filter properties
    filterSearch: string = '';
    filterAreaId: number | null = null;
    filterPersonalId: number | null = null;

    // Photo handling
    selectedFile: File | null = null;
    fotoPreview: string | null = null;

    currentPage = 1;
    lastPage = 1;
    pageSize = 10;
    totalReasignaciones = 0;

    form = {
        activo_id: null as number | null,
        personal_nuevo_id: null as number | null,
        area_nueva_id: null as number | null,
        motivo: ''
    };

    constructor(
        private reasignacionService: ReasignacionService,
        private personalService: PersonalService,
        private assetService: AssetService,
        private ubicacionService: UbicacionService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.loadData();
    }

    loadData() {
        this.loading = true;
        this.reasignacionService.getReasignaciones(this.currentPage).subscribe({
            next: (res: any) => {
                this.reasignaciones = res.data;
                this.currentPage = res.current_page;
                this.lastPage = res.last_page;
                this.totalReasignaciones = res.total;
                this.pageSize = res.per_page;
                this.loading = false;
                this.cdr.detectChanges();
            },
            error: () => {
                this.loading = false;
                this.cdr.detectChanges();
            }
        });

        this.personalService.getAllPersonal().subscribe(res => {
            // Ensure IDs are numbers
            this.personal = res.map((p: any) => ({ ...p, id: Number(p.id) }));
            this.cdr.detectChanges();
        });

        this.ubicacionService.getAllAreas().subscribe(res => {
            // Ensure IDs are numbers
            this.areas = res.map((a: any) => ({ ...a, id: Number(a.id) }));
            this.cdr.detectChanges();
        });

        this.assetService.getAssets(1, '', '', 2000).subscribe(res => {
            this.assets = res.data.map((a: any) => ({
                ...a,
                id: Number(a.id),
                area_id: a.area_id ? Number(a.area_id) : null,
                personal_id: a.personal_id ? Number(a.personal_id) : null
            }));
            this.cdr.detectChanges();
        });

        // Load classifications (if needed for list, but we removed from filter)
        this.assetService.getClasificaciones().subscribe(data => {
            this.clasificaciones = data;
            this.cdr.detectChanges();
        });
    }

    // Getter for filtered assets based on search, area, and personal filters
    get filteredAssets(): Asset[] {
        if (!this.filterSearch && !this.filterAreaId && !this.filterPersonalId) {
            return []; // Avoid showing thousands when no filters are applied
        }

        return this.assets.filter(a => {
            const searchLower = this.filterSearch.toLowerCase();
            const matchSearch = !this.filterSearch ||
                a.nombre_activo.toLowerCase().includes(searchLower) ||
                a.codigo_inventario.toLowerCase().includes(searchLower);

            const matchArea = !this.filterAreaId ||
                (a as any).area_id == this.filterAreaId;
            const matchPersonal = !this.filterPersonalId ||
                (a as any).personal_id == this.filterPersonalId;

            return matchSearch && matchArea && matchPersonal;
        });
    }

    // Getter for filtered personal based on selected area in Step 1
    get filteredCurrentPersonal(): Personal[] {
        if (!this.filterAreaId) return this.personal;
        return this.personal.filter(p => Number(p.area_id) === Number(this.filterAreaId));
    }

    // Getter for filtered personal based on selected new area in Step 2
    get filteredNewPersonal(): Personal[] {
        if (!this.form.area_nueva_id) return this.personal;
        return this.personal.filter(p => Number(p.area_id) === Number(this.form.area_nueva_id));
    }

    onFilterChange() {
        const query = this.filterSearch.toLowerCase().trim();
        if (!query) return;

        // INTELLIGENT AUTO-FILL
        // 1. Try exact match first
        const exactMatch = this.assets.find(a =>
            a.codigo_inventario.toLowerCase() === query ||
            a.nombre_activo.toLowerCase() === query
        );

        if (exactMatch) {
            // Force Number conversion to match [ngValue]
            this.filterAreaId = exactMatch.area_id ? Number(exactMatch.area_id) : null;
            this.filterPersonalId = exactMatch.personal_id ? Number(exactMatch.personal_id) : null;
            this.form.activo_id = Number(exactMatch.id);
            this.form.area_nueva_id = exactMatch.area_id ? Number(exactMatch.area_id) : null;
        } else {
            // 2. Try unique partial match if query is long enough
            const filtered = this.filteredAssets;
            if (query.length > 5 && filtered.length === 1) {
                const asset = filtered[0];
                this.filterAreaId = asset.area_id ? Number(asset.area_id) : null;
                this.filterPersonalId = asset.personal_id ? Number(asset.personal_id) : null;
                this.form.activo_id = Number(asset.id);
                this.form.area_nueva_id = asset.area_id ? Number(asset.area_id) : null;
            }
        }

        this.cdr.detectChanges();
    }

    onAssetSelect() {
        if (this.form.activo_id) {
            const asset = this.assets.find(a => a.id == this.form.activo_id);
            if (asset) {
                // Synchronize Step 1 filters with selected asset data
                this.filterAreaId = asset.area_id ? Number(asset.area_id) : null;
                this.filterPersonalId = asset.personal_id ? Number(asset.personal_id) : null;
                // Pre-fill NEW AREA in Step 2 with the current one as default
                this.form.area_nueva_id = asset.area_id ? Number(asset.area_id) : null;
            }
        }
        this.cdr.detectChanges();
    }

    onAreaNuevaChange() {
        // Reset personal selection when area changes
        this.form.personal_nuevo_id = null;
        this.cdr.detectChanges();
    }

    onFileSelected(event: any) {
        const file = event.target.files[0];
        if (file) {
            this.selectedFile = file;
            const reader = new FileReader();
            reader.onload = () => {
                this.fotoPreview = reader.result as string;
                this.cdr.detectChanges();
            };
            reader.readAsDataURL(file);
        }
    }

    removeFoto() {
        this.selectedFile = null;
        this.fotoPreview = null;
    }

    changePage(page: number) {
        if (page >= 1 && page <= this.lastPage) {
            this.currentPage = page;
            this.loadData();
        }
    }

    submitForm() {
        if (!this.form.activo_id || !this.form.personal_nuevo_id || !this.form.area_nueva_id || !this.form.motivo) {
            Swal.fire('Atención', 'Todos los campos son obligatorios', 'warning');
            return;
        }

        // Use FormData for multipart upload
        const formData = new FormData();
        formData.append('activo_id', (this.form.activo_id as any).toString());
        formData.append('personal_nuevo_id', (this.form.personal_nuevo_id as any).toString());
        formData.append('area_nueva_id', (this.form.area_nueva_id as any).toString());
        formData.append('motivo', this.form.motivo);

        if (this.selectedFile) {
            formData.append('foto', this.selectedFile);
        }

        this.reasignacionService.createReasignacion(formData).subscribe({
            next: () => {
                Swal.fire('Éxito', 'Reasignación completada exitosamente', 'success');
                this.showForm = false;
                this.form = { activo_id: null, personal_nuevo_id: null, area_nueva_id: null, motivo: '' };
                this.filterSearch = '';
                this.filterAreaId = null;
                this.filterPersonalId = null;
                this.removeFoto(); // Clear photo
                this.loadData();
                this.cdr.detectChanges();
            },
            error: (err) => {
                let errorMsg = 'No se pudo procesar la reasignación';
                if (err.status === 422 && err.error && err.error.errors) {
                    const errors = err.error.errors;
                    errorMsg = Object.values(errors).flat().join('\n');
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    }

    imprimirActa(id: number) {
        this.reasignacionService.downloadActa(id).subscribe({
            next: (blob) => {
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank');
                // Clean up the URL object after a short delay
                setTimeout(() => window.URL.revokeObjectURL(url), 1000);
            },
            error: (err) => {
                console.error('Error al descargar el acta', err);
                Swal.fire('Error', 'No se pudo descargar el acta', 'error');
            }
        });
    }

    verDetalles(item: any) {
        this.selectedReasignacion = item;
        this.showDetails = true;
        this.cdr.detectChanges();
    }

    getFotoUrl(path: string): string {
        if (!path) return '';
        if (path.startsWith('http')) return path;

        // Use sibling path since storage is a sibling of the SGAF2 app folder
        return `../storage/${path.replace('public/', '')}`;
    }

    getAssetBadge(activo: string): string {
        return activo ? 'badge-blue' : 'badge-gray';
    }

    formatDate(date: string): string {
        return new Date(date).toLocaleString('es-NI', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        }).replace(',', '');
    }
}
