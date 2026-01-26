import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { TrazabilidadService } from '../../services/trazabilidad.service';
import { environment } from '../../../environments/environment';

@Component({
    selector: 'app-trazabilidad',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './trazabilidad.component.html'
})
export class TrazabilidadComponent implements OnInit {
    historial: any[] = [];
    loading = false;
    search = '';

    currentPage = 1;
    lastPage = 1;
    pageSize = 10;
    totalItems = 0;

    constructor(
        private trazabilidadService: TrazabilidadService,
        private cdr: ChangeDetectorRef
    ) { }

    ngOnInit() {
        this.loadData();
    }

    loadData() {
        this.loading = true;
        this.trazabilidadService.getHistorial(this.search, this.currentPage).subscribe({
            next: (res: any) => {
                this.historial = res.data;
                this.currentPage = res.current_page;
                this.lastPage = res.last_page;
                this.totalItems = res.total;
                this.pageSize = res.per_page;
                this.loading = false;
                this.cdr.detectChanges();
            },
            error: () => {
                this.loading = false;
                this.cdr.detectChanges();
            }
        });
    }

    changePage(page: number) {
        if (page >= 1 && page <= this.lastPage) {
            this.currentPage = page;
            this.loadData();
        }
    }

    onSearch() {
        this.currentPage = 1;
        this.loadData();
    }

    formatDate(date: string): string {
        return new Date(date).toLocaleDateString('es-NI', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    getImageUrl(path: string): string {
        if (!path) return '';
        // If it starts with http, it's already a full URL
        if (path.startsWith('http')) return path;

        // Clean path: remove leading /storage/ if present, and any leading slashes
        let cleanPath = path.replace(/^\/?storage\//, '').replace(/^\//, '');

        // Construct URL from Laravel public storage
        return `${environment.apiUrl.replace('/api', '')}/storage/${cleanPath}`;
    }
}
