import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { TrazabilidadService } from '../../services/trazabilidad.service';

@Component({
    selector: 'app-trazabilidad',
    standalone: true,
    imports: [CommonModule, FormsModule, MainLayoutComponent],
    templateUrl: './trazabilidad.component.html'
})
export class TrazabilidadComponent implements OnInit {
    historial: any[] = [];
    loading = false;
    search = '';

    constructor(
        private trazabilidadService: TrazabilidadService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.loadData();
    }

    loadData() {
        this.loading = true;
        this.trazabilidadService.getHistorial().subscribe({
            next: (res: any) => {
                this.historial = res.data;
                this.loading = false;
                this.cdr.detectChanges();
            },
            error: () => {
                this.loading = false;
                this.cdr.detectChanges();
            }
        });
    }

    onSearch() {
        this.loading = true;
        this.trazabilidadService.getHistorial(this.search).subscribe({
            next: (res: any) => {
                this.historial = res.data;
                this.loading = false;
                this.cdr.detectChanges();
            }
        });
    }

    formatDate(date: string): string {
        return new Date(date).toLocaleDateString('es-NI', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    }
}
