import { Component, OnInit, ChangeDetectorRef, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DepreciacionService } from '../../services/depreciacion.service';
import { Subject, debounceTime, distinctUntilChanged, takeUntil } from 'rxjs';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-depreciacion',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './depreciacion.component.html'
})
export class DepreciacionComponent implements OnInit, OnDestroy {
    activos: any[] = [];
    loading = true;
    totalValorOriginal = 0;
    totalDepreciacion = 0;
    totalValorActual = 0;

    // Tabs
    activeTab: 'configurados' | 'sin-configurar' = 'configurados';

    // Pagination & Search
    currentPage = 1;
    totalPages = 1;
    totalItems = 0;
    perPage = 15;
    searchTerm = '';
    private searchSubject = new Subject<string>();
    private destroy$ = new Subject<void>();

    // Catalog & Modal
    catalogo: any[] = [];
    showModal = false;
    selectedAsset: any = null;
    selectedCategory: any = null;
    saving = false;
    fechaCorteManual: string = new Date().toISOString().split('T')[0];
    vidaUtilManual: number = 0;

    constructor(
        private depreciacionService: DepreciacionService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.setupSearch();
        this.loadData();
        this.loadCatalogo();
    }

    ngOnDestroy() {
        this.destroy$.next();
        this.destroy$.complete();
    }

    private setupSearch() {
        this.searchSubject.pipe(
            debounceTime(500),
            distinctUntilChanged(),
            takeUntil(this.destroy$)
        ).subscribe(term => {
            this.searchTerm = term;
            this.currentPage = 1;
            this.loadData();
        });
    }

    onSearchChange(event: any) {
        this.searchSubject.next(event.target.value);
    }

    setTab(tab: 'configurados' | 'sin-configurar') {
        this.activeTab = tab;
        this.currentPage = 1;
        this.loadData();
    }

    loadCatalogo() {
        this.depreciacionService.getCatalogo().subscribe(data => this.catalogo = data);
    }

    loadData(page: number = 1) {
        this.loading = true;
        this.currentPage = page;

        const request = this.activeTab === 'configurados'
            ? this.depreciacionService.getDepreciacion(page, this.searchTerm)
            : this.depreciacionService.getSinConfigurar(page, this.searchTerm);

        request.subscribe({
            next: (response: any) => {
                this.activos = response.data || [];
                this.totalItems = response.total || 0;
                this.totalPages = response.last_page || 1;
                this.currentPage = response.current_page || 1;

                this.calculateTotals();
                this.loading = false;
                this.cdr.detectChanges();
            },
            error: () => {
                this.loading = false;
                this.cdr.detectChanges();
            }
        });
    }

    openConfigModal(asset: any) {
        this.selectedAsset = asset;
        this.selectedAsset.valor_residual_manual = 0;
        this.vidaUtilManual = 0;
        this.fechaCorteManual = new Date().toISOString().split('T')[0];
        this.selectedCategory = null;
        this.showModal = true;
    }

    selectCategory(cat: any) {
        this.selectedCategory = cat;
        this.vidaUtilManual = cat.vida_util_anos;
    }

    closeModal() {
        this.showModal = false;
        this.selectedAsset = null;
        this.selectedCategory = null;
    }

    saveConfig() {
        if (!this.selectedAsset || !this.selectedCategory) return;

        this.saving = true;
        const config = {
            vida_util_anos: this.vidaUtilManual,
            valor_residual: this.selectedAsset.valor_residual_manual || 0,
            metodo_depreciacion: 'linea_recta',
            fecha_corte: this.fechaCorteManual,
            fecha_adquisicion: this.selectedAsset.fecha_adquisicion
        };

        this.depreciacionService.configurar(this.selectedAsset.id, config).subscribe({
            next: (res: any) => {
                this.saving = false;
                this.closeModal();
                Swal.fire('Configurado', res.message, 'success');
                this.loadData();
            },
            error: () => {
                this.saving = false;
            }
        });
    }


    resetDepreciacion() {
        Swal.fire({
            title: '¿Reiniciar Depreciación?',
            text: "Se pondrán en cero todos los valores de depreciación acumulada y se restaurará el valor en libros al precio original. Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, reiniciar todo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.loading = true;
                this.depreciacionService.resetear().subscribe({
                    next: (res) => {
                        Swal.fire('¡Reiniciado!', res.message, 'success');
                        this.loadData();
                    },
                    error: () => {
                        this.loading = false;
                        Swal.fire('Error', 'No se pudo reiniciar la depreciación.', 'error');
                    }
                });
            }
        });
    }

    desconfigurarTodo() {
        Swal.fire({
            title: '¿DESCONFIGURAR TODO?',
            text: "Esta acción ELIMINARÁ la vida útil y los parámetros de depreciación de TODOS los activos. Todo el inventario pasará a la pestaña 'Sin Configurar'.",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, borrar configuraciones',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.loading = true;
                this.depreciacionService.limpiarTodo().subscribe({
                    next: (res) => {
                        Swal.fire('¡Limpieza Completa!', res.message, 'success');
                        this.loadData();
                    },
                    error: () => {
                        this.loading = false;
                        Swal.fire('Error', 'No se pudo realizar la limpieza global.', 'error');
                    }
                });
            }
        });
    }

    procesarDepreciacion() {
        Swal.fire({
            title: 'Procesar Cierre de Depreciación',
            html: `
                <div class="text-left space-y-4">
                    <p class="text-sm text-gray-600">Se calculará la depreciación acumulada para TODOS los activos configurados.</p>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-500 uppercase">Año de Corte / Cierre</label>
                        <input id="fechaCorte" type="date" class="swal2-input !mt-0 !w-full" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Procesar Ahora',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                return (document.getElementById('fechaCorte') as HTMLInputElement).value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.loading = true;
                this.depreciacionService.procesarMasivo(result.value).subscribe({
                    next: (res) => {
                        Swal.fire('¡Completado!', res.message, 'success');
                        this.loadData();
                    },
                    error: () => {
                        this.loading = false;
                        Swal.fire('Error', 'Ocurrió un error durante el proceso masivo.', 'error');
                    }
                });
            }
        });
    }

    calculateTotals() {
        if (this.activeTab === 'sin-configurar') {
            this.totalValorOriginal = this.activos.reduce((sum, a) => sum + (parseFloat(a.precio_adquisicion) || 0), 0);
            this.totalDepreciacion = 0;
            this.totalValorActual = this.totalValorOriginal;
            return;
        }
        this.totalValorOriginal = this.activos.reduce((sum, a) => sum + (parseFloat(a.precio_adquisicion) || 0), 0);
        this.totalDepreciacion = this.activos.reduce((sum, a) => sum + (parseFloat(a.depreciacion_acumulada) || 0), 0);
        this.totalValorActual = this.activos.reduce((sum, a) => sum + (parseFloat(a.valor_libros) || 0), 0);
    }

    formatCurrency(value: number): string {
        return new Intl.NumberFormat('es-NI', {
            style: 'currency',
            currency: 'NIO',
            minimumFractionDigits: 2
        }).format(value);
    }

    getDepreciationWidth(item: any): string {
        const original = parseFloat(item.precio_adquisicion) || 1;
        const dep = parseFloat(item.depreciacion_acumulada) || 0;
        const percent = (dep / original) * 100;
        return `${Math.min(percent, 100)}%`;
    }

    getProgressColor(item: any): string {
        const original = parseFloat(item.precio_adquisicion) || 1;
        const dep = parseFloat(item.depreciacion_acumulada) || 0;
        const percent = (dep / original) * 100;
        if (percent > 80) return 'bg-gradient-to-r from-red-500 to-rose-600 shadow-[0_0_10px_rgba(239,68,68,0.5)]';
        if (percent > 50) return 'bg-gradient-to-r from-orange-400 to-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]';
        return 'bg-gradient-to-r from-red-600 to-red-500 shadow-[0_0_10px_rgba(220,38,38,0.5)]';
    }
}
