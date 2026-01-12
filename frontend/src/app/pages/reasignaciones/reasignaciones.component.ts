import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { ReasignacionService } from '../../services/reasignacion.service';
import { PersonalService, Personal } from '../../services/personal.service';
import { AssetService } from '../../services/asset.service';
import { Asset } from '../../models/asset.model';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-reasignaciones',
    standalone: true,
    imports: [CommonModule, FormsModule, MainLayoutComponent],
    templateUrl: './reasignaciones.component.html'
})
export class ReasignacionesComponent implements OnInit {
    reasignaciones: any[] = [];
    personal: Personal[] = [];
    assets: Asset[] = [];
    loading = true;
    showForm = false;

    currentPage = 1;
    lastPage = 1;
    pageSize = 10;
    totalReasignaciones = 0;

    form = {
        activo_id: null as number | null,
        personal_nuevo_id: null as number | null,
        motivo: ''
    };

    constructor(
        private reasignacionService: ReasignacionService,
        private personalService: PersonalService,
        private assetService: AssetService,
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

        this.personalService.getAllPersonal().subscribe(data => {
            this.personal = data;
            this.cdr.detectChanges();
        });

        this.assetService.getAssets(1, '', 'BUENO').subscribe(res => {
            this.assets = res.data;
            this.cdr.detectChanges();
        });
    }

    changePage(page: number) {
        if (page >= 1 && page <= this.lastPage) {
            this.currentPage = page;
            this.loadData();
        }
    }

    submitForm() {
        if (!this.form.activo_id || !this.form.personal_nuevo_id || !this.form.motivo) {
            Swal.fire('Atención', 'Todos los campos son obligatorios', 'warning');
            return;
        }

        this.reasignacionService.createReasignacion(this.form).subscribe({
            next: () => {
                Swal.fire('Éxito', 'Reasignación completada exitosamente', 'success');
                this.showForm = false;
                this.form = { activo_id: null, personal_nuevo_id: null, motivo: '' };
                this.loadData();
            },
            error: () => Swal.fire('Error', 'No se pudo procesar la reasignación', 'error')
        });
    }

    getAssetBadge(activo: string): string {
        return activo ? 'badge-blue' : 'badge-gray';
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
}
