import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AssetService } from '../../services/asset.service';
import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { PersonalService, Personal } from '../../services/personal.service';
import { UbicacionService } from '../../services/ubicacion.service';
import { Asset } from '../../models/asset.model';
import { environment } from '../../../environments/environment';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-activos-state',
    standalone: true,
    imports: [CommonModule, FormsModule, RouterModule],
    templateUrl: './activos-state.component.html',
})
export class ActivosStateComponent implements OnInit {
    currentState: 'DESUSO' | 'BAJA' = 'DESUSO';
    pageTitle: string = 'Activos en Desuso';
    pageDescription: string = 'Gestión de activos marcados como no operativos.';

    // Main list (history/current items)
    assets: any[] = [];
    loading: boolean = true;
    searchTerm: string = '';

    // Pagination
    currentPage: number = 1;
    pageSize: number = 10;
    totalAssets: number = 0;
    lastPage: number = 1;

    // --- FORM / 2-STEP PROCESS PROPERTIES ---
    showForm = false;
    loadingSource = false;

    // Source assets (candidates for state change)
    sourceAssets: Asset[] = [];
    personal: Personal[] = [];
    areas: any[] = [];

    // Filters for Step 1
    filterSearch: string = '';
    filterAreaId: number | null = null;
    filterPersonalId: number | null = null;

    // Form Data for Step 2
    form = {
        activo_id: null as number | null,
        motivo: '',
        foto: null as File | null
    };

    fotoPreview: string | null = null;
    storageUrl = environment.apiUrl.replace('/api', '') + '/storage/';

    constructor(
        private assetService: AssetService,
        private personalService: PersonalService,
        private ubicacionService: UbicacionService,
        private route: ActivatedRoute,
        private router: Router,
        private cdr: ChangeDetectorRef,
        private http: HttpClient
    ) { }

    ngOnInit(): void {
        // Read configuration from Route Data
        this.route.data.subscribe(data => {
            this.currentState = data['state'] || 'DESUSO';
            this.pageTitle = data['title'] || 'Activos';
            this.pageDescription = this.currentState === 'DESUSO'
                ? 'Gestión de activos marcados como no operativos o en pausa.'
                : 'Histórico de activos dados de baja definitiva del inventario.';

            // Reset and reload when route changes
            this.currentPage = 1;
            this.searchTerm = '';
            this.showForm = false; // Reset form view
            this.loadAssets();
            this.loadAuxiliaryData();
            this.loadSourceAssets(); // Pre-fetch source assets for the form
        });
    }

    // Load auxiliary data for filters
    loadAuxiliaryData() {
        this.personalService.getAllPersonal().subscribe(res => {
            this.personal = res.map((p: any) => ({ ...p, id: Number(p.id) }));
            this.cdr.detectChanges();
        });

        this.ubicacionService.getAllAreas().subscribe(res => {
            this.areas = res.map((a: any) => ({ ...a, id: Number(a.id) }));
            this.cdr.detectChanges();
        });
    }

    // Load assets that CAN be moved to the current state
    loadSourceAssets() {
        this.loadingSource = true;
        // For DESUSO: We can move 'BUENO', 'REGULAR', 'MALO' (everything not DESUSO or BAJA)
        // For BAJA: We can move everything not already BAJA (including DESUSO)
        // Since API filters by exact state, we might need a way to fetch "Active" assets or exclude current state.
        // For now, let's fetch ALL (empty state) and filter client side. Sucks for performance but aligns with current Reasignaciones.
        // Or if we improve the backend later.

        // We limit to 2000 like Reasignaciones
        this.assetService.getAssets(1, '', '', 2000).subscribe({
            next: (res: any) => {
                const allAssets = res.data.map((a: any) => ({
                    ...a,
                    id: Number(a.id),
                    area_id: a.area_id ? Number(a.area_id) : null,
                    personal_id: a.personal_id ? Number(a.personal_id) : null
                }));

                // Client-side filter to only show eligible assets
                this.sourceAssets = allAssets.filter((a: any) => {
                    if (this.currentState === 'DESUSO') {
                        // Can move to DESUSO if NOT already DESUSO and NOT BAJA
                        return a.estado !== 'DESUSO' && a.estado !== 'BAJA';
                    } else if (this.currentState === 'BAJA') {
                        // Can move to BAJA if NOT already BAJA
                        return a.estado !== 'BAJA';
                    }
                    return true;
                });

                this.loadingSource = false;
                this.cdr.detectChanges();
            },
            error: () => {
                this.loadingSource = false;
            }
        });
    }

    loadAssets(): void {
        this.loading = true;
        this.assetService.getAssets(
            this.currentPage,
            this.searchTerm,
            this.currentState,
            this.pageSize
        ).subscribe({
            next: (res: any) => {
                this.assets = res.data;

                if (res.meta) {
                    this.currentPage = res.meta.current_page;
                    this.lastPage = res.meta.last_page;
                    this.totalAssets = res.meta.total;
                    this.pageSize = res.meta.per_page;
                } else {
                    this.currentPage = res.current_page;
                    this.lastPage = res.last_page;
                    this.totalAssets = res.total;
                }

                this.loading = false;
                this.cdr.detectChanges();
            },
            error: (err) => {
                console.error(err);
                this.loading = false;
                Swal.fire('Error', 'No se pudieron cargar los activos', 'error');
            }
        });
    }

    onSearch(): void {
        this.currentPage = 1;
        this.loadAssets();
    }

    onPageChange(page: number): void {
        this.currentPage = page;
        this.loadAssets();
    }

    // --- FORM HELPERS (Copied/Adapted from Reasignaciones) ---

    // Getter for filtered source assets
    get filteredSourceAssets(): Asset[] {
        if (!this.filterSearch && !this.filterAreaId && !this.filterPersonalId) {
            return [];
        }

        return this.sourceAssets.filter(a => {
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

    get filteredCurrentPersonal(): Personal[] {
        if (!this.filterAreaId) return this.personal;
        return this.personal.filter(p => Number(p.area_id) === Number(this.filterAreaId));
    }

    onFilterChange() {
        const query = this.filterSearch.toLowerCase().trim();
        if (!query) return;

        // Auto-select if exact match
        const exactMatch = this.sourceAssets.find(a =>
            a.codigo_inventario.toLowerCase() === query ||
            a.nombre_activo.toLowerCase() === query
        );

        if (exactMatch) {
            this.filterAreaId = exactMatch.area_id ? Number(exactMatch.area_id) : null;
            this.filterPersonalId = exactMatch.personal_id ? Number(exactMatch.personal_id) : null;
            this.form.activo_id = Number(exactMatch.id);
        } else {
            // Try unique partial match
            const filtered = this.filteredSourceAssets;
            if (query.length > 5 && filtered.length === 1) {
                const asset = filtered[0];
                this.filterAreaId = asset.area_id ? Number(asset.area_id) : null;
                this.filterPersonalId = asset.personal_id ? Number(asset.personal_id) : null;
                this.form.activo_id = Number(asset.id);
            }
        }
        this.cdr.detectChanges();
    }

    onAssetSelect() {
        if (this.form.activo_id) {
            const asset = this.sourceAssets.find(a => a.id == this.form.activo_id);
            if (asset) {
                this.filterAreaId = asset.area_id ? Number(asset.area_id) : null;
                this.filterPersonalId = asset.personal_id ? Number(asset.personal_id) : null;
            }
        }
    }

    onFileSelected(event: any) {
        const file = event.target.files[0];
        if (file) {
            this.form.foto = file;
            const reader = new FileReader();
            reader.onload = () => {
                this.fotoPreview = reader.result as string;
                this.cdr.detectChanges();
            };
            reader.readAsDataURL(file);
        }
    }

    removeFoto() {
        this.form.foto = null;
        this.fotoPreview = null;
    }

    // --- SUBMIT ---

    submitForm() {
        if (!this.form.activo_id || !this.form.motivo) {
            Swal.fire('Atención', 'Selecciona un activo y escribe un motivo.', 'warning');
            return;
        }

        const actionText = this.currentState === 'DESUSO' ? 'Enviar a Desuso' : 'Dar de Baja';

        Swal.fire({
            title: `¿${actionText}?`,
            text: 'Esta acción actualizará el estado del activo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.executeStateChange();
            }
        });
    }

    executeStateChange() {
        const assetId = this.form.activo_id!;
        const currentAsset = this.sourceAssets.find(a => a.id == assetId);

        const newDesc = (currentAsset ? (currentAsset.descripcion || '') : '') + `\n[${new Date().toLocaleDateString()}] Cambio a ${this.currentState}: ${this.form.motivo}`;

        if (this.form.foto) {
            const formData = new FormData();
            formData.append('_method', 'PUT'); // Trick for Laravel
            formData.append('estado', this.currentState);
            formData.append('descripcion', newDesc);
            formData.append('foto', this.form.foto);

            this.httpPostAsPut(assetId, formData);
        } else {
            // Simple path: Partial JSON update
            const payload = {
                estado: this.currentState,
                descripcion: newDesc
            };

            this.assetService.updateAsset(assetId, payload).subscribe({
                next: () => this.handleSuccess(),
                error: (err) => this.handleError(err)
            });
        }
    }

    httpPostAsPut(id: number, formData: FormData) {
        this.http.post(`${environment.apiUrl}/assets/${id}`, formData).subscribe({
            next: () => this.handleSuccess(),
            error: (err) => this.handleError(err)
        });
    }

    handleSuccess() {
        Swal.fire('Éxito', 'Estado actualizado correctamente', 'success');
        this.showForm = false;
        this.form = { activo_id: null, motivo: '', foto: null };
        this.removeFoto();
        this.loadAssets(); // Refresh main list
        this.loadSourceAssets(); // Refresh eligible list
    }

    handleError(err: any) {
        console.error(err);
        Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
    }

    // --- Actions (from table) ---

    // Desuso -> Reactivar (Bueno)
    reactivarActivo(asset: any): void {
        Swal.fire({
            title: '¿Reactivar Activo?',
            text: `El activo "${asset.nombre_activo}" volverá al inventario operativo.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, reactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.updateStateDirect(asset, 'BUENO');
            }
        });
    }

    // Desuso -> Baja (Shortcut from table)
    darDeBaja(asset: any): void {
        // Instead of direct action, maybe open the form pre-filled?
        // For now, keep direct action for convenience, or redirect to form?
        // Let's keep direct action as "Quick Action"
        Swal.fire({
            title: '¿Dar de Baja?',
            text: 'Esta acción moverá el activo a bajas.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, dar de baja',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.updateStateDirect(asset, 'BAJA');
            }
        });
    }

    restaurarDeBaja(asset: any): void {
        Swal.fire({
            title: '¿Restaurar?',
            text: 'El activo pasará a DESUSO.',
            showCancelButton: true,
            confirmButtonText: 'Sí, restaurar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.updateStateDirect(asset, 'DESUSO');
            }
        });
    }

    private updateStateDirect(asset: any, newState: string): void {
        const payload = { ...asset };
        payload.estado = newState;
        // We need to ensure we send all required fields or rely on backend ignoring missing ones if we used PATCH (but we use PUT usually)
        // asset object from list usually has all fields.

        this.assetService.updateAsset(asset.id, payload).subscribe({
            next: () => {
                Swal.fire('Completado', 'Estado actualizado.', 'success');
                this.loadAssets();
                this.loadSourceAssets();
            },
            error: () => Swal.fire('Error', 'No se pudo actualizar.', 'error')
        });
    }

    getBadgeClass(state: string): string {
        switch (state) {
            case 'DESUSO': return 'badge-warning';
            case 'BAJA': return 'badge-danger';
            default: return 'badge-gray';
        }
    }
}
