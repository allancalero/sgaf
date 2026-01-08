import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { DashboardService } from '../../services/dashboard.service';
import { DashboardStats } from '../../models/dashboard-stats';

@Component({
    selector: 'app-dashboard',
    standalone: true,
    imports: [CommonModule, RouterModule, MainLayoutComponent],
    templateUrl: './dashboard.component.html'
})
export class DashboardComponent implements OnInit {
    stats: DashboardStats | null = null;
    loading = true;
    error: string | null = null;

    constructor(
        private dashboardService: DashboardService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.loadDashboard();
    }

    loadDashboard() {
        console.log('DashboardComponent: Fetching stats...');
        this.dashboardService.getStats().subscribe({
            next: (data) => {
                console.log('DashboardComponent: Received data:', data);
                this.stats = data;
                this.loading = false;
                // Force change detection
                this.cdr.detectChanges();
                console.log('DashboardComponent: Stats assigned, totalActivos =', this.stats?.totalActivos);
            },
            error: (err) => {
                console.error('DashboardComponent: Error fetching stats:', err);
                this.error = err.message || 'Error loading data';
                this.loading = false;
                this.cdr.detectChanges();
            }
        });
    }

    formatCurrency(value: number): string {
        return new Intl.NumberFormat('es-NI', {
            style: 'currency',
            currency: 'NIO',
            minimumFractionDigits: 2
        }).format(value);
    }

    getBuenosCount(): number {
        if (!this.stats?.activosPorEstado) return 0;
        const bueno = this.stats.activosPorEstado.find(e => e.estado === 'BUENO');
        return bueno?.cantidad || 0;
    }

    getMalosCount(): number {
        if (!this.stats?.activosPorEstado) return 0;
        const malo = this.stats.activosPorEstado.find(e => e.estado === 'MALO');
        return malo?.cantidad || 0;
    }
}
