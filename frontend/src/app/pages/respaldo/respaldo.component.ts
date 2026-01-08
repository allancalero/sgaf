import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { SistemaService } from '../../services/sistema.service';

@Component({
    selector: 'app-respaldo',
    standalone: true,
    imports: [CommonModule, MainLayoutComponent],
    template: `
        <app-main-layout>
            <div class="p-6 lg:p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Respaldo</h2>
                    <p class="text-sm text-gray-500">Respaldo y restauración de datos</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Información del Sistema</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <p class="text-xs uppercase text-gray-500 mb-1">Base de Datos</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ info?.database || 'N/A' }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <p class="text-xs uppercase text-gray-500 mb-1">Directorio de Respaldos</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100 text-xs break-all">{{ info?.storage_path || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Respaldos Disponibles</h3>
                    </div>
                    <div class="p-6" *ngIf="backups.length === 0">
                        <p class="text-gray-500 text-center">No hay respaldos disponibles</p>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700" *ngIf="backups.length > 0">
                        <div *ngFor="let b of backups" class="px-6 py-4 flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ b.nombre }}</p>
                                <p class="text-sm text-gray-500">{{ b.fecha }}</p>
                            </div>
                            <span class="text-sm text-gray-500">{{ (b.size / 1024 / 1024) | number:'1.2-2' }} MB</span>
                        </div>
                    </div>
                </div>
            </div>
        </app-main-layout>
    `
})
export class RespaldoComponent implements OnInit {
    info: any = null;
    backups: any[] = [];

    constructor(private sistemaService: SistemaService) { }

    ngOnInit() {
        this.sistemaService.getRespaldo().subscribe(res => {
            this.info = res;
            this.backups = res.backups || [];
        });
    }
}
