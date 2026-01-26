import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SistemaService } from '../../services/sistema.service';

import Swal from 'sweetalert2';

@Component({
    selector: 'app-respaldo',
    standalone: true,
    imports: [CommonModule],
    template: `
            <div class="p-6 lg:p-8">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 italic">Respaldo</h2>
                        <p class="text-sm text-gray-500">Respaldo y restauración de datos</p>
                    </div>
                    <button 
                        (click)="generarRespaldo()" 
                        [disabled]="isGenerating"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-500/30 transition-all active:scale-95 disabled:opacity-50"
                    >
                        <svg *ngIf="isGenerating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <i *ngIf="!isGenerating" class="fas fa-database"></i>
                        {{ isGenerating ? 'Generando...' : 'GENERAR RESPALDO' }}
                    </button>
                </div>

                <!-- Info Cards -->
                <div class="grid gap-6 sm:grid-cols-2 mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl">
                                <i class="fas fa-server text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Base de Datos</p>
                                <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ info?.database || 'Calculando...' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                                <i class="fas fa-folder-open text-xl"></i>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Directorio de Almacenamiento</p>
                                <p class="text-sm font-bold text-gray-600 dark:text-gray-300 truncate">{{ info?.storage_path || 'Buscando...' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                            <i class="fas fa-history text-indigo-500"></i>
                            Historial de Respaldos
                        </h3>
                        <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-full">
                            {{ backups.length }} Archivos
                        </span>
                    </div>

                    <div class="p-12 text-center" *ngIf="backups.length === 0">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                            <i class="fas fa-box-open text-3xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No se han encontrado respaldos en el servidor.</p>
                        <p class="text-xs text-gray-400">Haga clic en el botón superior para crear uno nuevo.</p>
                    </div>

                    <div class="overflow-x-auto" *ngIf="backups.length > 0">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs font-bold text-gray-400 uppercase tracking-wider bg-gray-50 dark:bg-gray-900/50">
                                    <th class="px-6 py-4">Archivo</th>
                                    <th class="px-6 py-4">Fecha de Creación</th>
                                    <th class="px-6 py-4">Tamaño</th>
                                    <th class="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr *ngFor="let b of backups" class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg group-hover:scale-110 transition-transform">
                                                <i class="fas fa-file-code"></i>
                                            </div>
                                            <span class="font-bold text-gray-700 dark:text-gray-200">{{ b.nombre }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ b.fecha }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold rounded-lg">
                                            {{ (b.size / 1024 / 1024) | number:'1.2-2' }} MB
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button 
                                                (click)="descargar(b.nombre)"
                                                class="w-9 h-9 flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-lg hover:bg-emerald-600 hover:text-white transition-all shadow-sm"
                                                title="Descargar"
                                            >
                                                <i class="fas fa-download text-sm"></i>
                                            </button>
                                            <button 
                                                (click)="eliminar(b.nombre)"
                                                class="w-9 h-9 flex items-center justify-center bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 rounded-lg hover:bg-rose-600 hover:text-white transition-all shadow-sm"
                                                title="Eliminar"
                                            >
                                                <i class="fas fa-trash-alt text-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
                    title: '¡Generado!',
                    text: 'El respaldo se ha creado exitosamente.',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#f8fafc',
                    customClass: {
                        title: 'text-indigo-600 font-bold',
                        popup: 'rounded-2xl border-2 border-indigo-100'
                    }
                });
                this.loadData();
            },
            error: (err) => {
                this.isGenerating = false;
                console.error('Error generando respaldo', err);
                const errorMsg = err.error?.error || err.error?.message || 'Error desconocido';
                Swal.fire({
                    icon: 'error',
                    title: 'Error Crítico',
                    text: errorMsg,
                    background: '#fff1f2',
                    customClass: {
                        title: 'text-rose-600 font-bold',
                        popup: 'rounded-2xl border-2 border-rose-100'
                    }
                });
            }
        });
    }

    descargar(filename: string) {
        this.sistemaService.descargarRespaldo(filename).subscribe({
            next: (blob) => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            },
            error: (err) => {
                console.error('Error al descargar respaldo', err);
                Swal.fire('Error', 'No se pudo descargar el archivo.', 'error');
            }
        });
    }

    eliminar(filename: string) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Vas a eliminar permanentemente el respaldo: ${filename}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            background: '#f8fafc',
            customClass: {
                popup: 'rounded-2xl border-2 border-gray-100'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.sistemaService.eliminarRespaldo(filename).subscribe({
                    next: () => {
                        this.loadData();
                        Swal.fire({
                            title: 'Eliminado',
                            text: 'El archivo ha sido borrado.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: (err) => {
                        Swal.fire('Error', 'No se pudo eliminar el archivo.', 'error');
                        console.error(err);
                    }
                });
            }
        });
    }
}
