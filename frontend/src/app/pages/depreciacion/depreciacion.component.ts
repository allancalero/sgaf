import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { DepreciacionService } from '../../services/depreciacion.service';

@Component({
    selector: 'app-depreciacion',
    standalone: true,
    imports: [CommonModule, MainLayoutComponent],
    templateUrl: './depreciacion.component.html'
})
export class DepreciacionComponent implements OnInit {
    activos: any[] = [];
    loading = true;
    totalValorOriginal = 0;
    totalDepreciacion = 0;
    totalValorActual = 0;

    constructor(
        private depreciacionService: DepreciacionService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.loadData();
    }

    loadData() {
        this.loading = true;
        this.depreciacionService.getDepreciacion().subscribe({
            next: (data: any[]) => {
                this.activos = data;
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

    calculateTotals() {
        this.totalValorOriginal = this.activos.reduce((sum, a) => sum + (parseFloat(a.valor_adquisicion) || 0), 0);
        this.totalDepreciacion = this.activos.reduce((sum, a) => sum + (parseFloat(a.depreciacion_acumulada) || 0), 0);
        this.totalValorActual = this.activos.reduce((sum, a) => sum + (parseFloat(a.valor_libro) || 0), 0);
    }

    formatCurrency(value: number): string {
        return new Intl.NumberFormat('es-NI', {
            style: 'currency',
            currency: 'NIO',
            minimumFractionDigits: 2
        }).format(value);
    }

    getDepreciationWidth(item: any): string {
        const original = parseFloat(item.valor_adquisicion) || 1;
        const dep = parseFloat(item.depreciacion_acumulada) || 0;
        const percent = (dep / original) * 100;
        return `${Math.min(percent, 100)}%`;
    }
}
