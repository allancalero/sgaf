import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { SistemaService } from '../../services/sistema.service';

import Swal from 'sweetalert2';

@Component({
    selector: 'app-respaldo',
    standalone: true,
    imports: [CommonModule, MainLayoutComponent],
    template: `
        <app-main-layout>
            <div class="p-6 lg:p-8">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Respaldo</h2>
                        <p class="text-sm text-gray-500">Respaldo y restauración de datos</p>
                    </div>
                    <button 
                        (click)="generarRespaldo()" 
                        [disabled]="isGenerating"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        <svg *ngIf="isGenerating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg *ngIf="!isGenerating" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        {{ isGenerating ? 'Generando...' : 'Generar Respaldo' }}
                    </button>
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
    isGenerating = false;

    constructor(private sistemaService: SistemaService) { }

    ngOnInit() {
        this.loadData();
    }

    loadData() {
        this.sistemaService.getRespaldo().subscribe({
            next: (res) => {
                this.info = res;
                this.backups = res.backups || [];
            },
            error: (err) => {
                console.error('Error cargando respaldos', err);
            }
        });
    }

    generarRespaldo() {
        this.isGenerating = true;
        this.sistemaService.generarRespaldo().subscribe({
            next: (res) => {
                this.isGenerating = false;
                Swal.fire({
                    icon: 'success',
                    title: 'Respaldo Generado',
                    text: res.message || 'El respaldo se ha creado correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
                this.loadData();
            },
            error: (err) => {
                this.isGenerating = false;
                console.error('Error generando respaldo', err);
                const errorMsg = err.error?.error || err.error?.message || 'Error desconocido';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg,
                    customClass: {
                        popup: 'swal-wide'
                    }
                });
            }
        });
    }
}
