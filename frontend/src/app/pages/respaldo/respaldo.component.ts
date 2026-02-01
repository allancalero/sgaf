import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SistemaService } from '../../services/sistema.service';

import Swal from 'sweetalert2';

@Component({
    selector: 'app-respaldo',
    standalone: true,
    imports: [CommonModule],
    template: `
            <div class="p-6 lg:p-10 min-h-screen">
                <div class="mb-10 animate-fade-in flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div>
                        <h1 class="text-5xl font-orbitron font-black text-white tracking-tighter uppercase leading-none">
                            Respaldo <span class="text-red-600">de Datos</span>
                        </h1>
                        <p class="text-red-500/80 mt-3 text-sm font-black uppercase tracking-[0.3em]">
                            Gestión integral de copias de seguridad y recuperación.
                        </p>
                    </div>
                    <button 
                        (click)="generarRespaldo()" 
                        [disabled]="isGenerating"
                        class="btn-primary px-8 py-4 !shadow-red-600/30 flex-none"
                    >
                        <i *ngIf="!isGenerating" class="fas fa-database mr-2"></i>
                        <i *ngIf="isGenerating" class="fas fa-circle-notch animate-spin mr-2"></i>
                        {{ isGenerating ? 'GENERANDO...' : 'GENERAR RESPALDO' }}
                    </button>
                </div>

                <!-- Info Cards Radiant -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                    <div class="radiant-card-stat">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-red-600/10 rounded-2xl border border-red-600/20 shadow-lg shadow-red-600/10">
                                <i class="fas fa-server text-xl text-red-500"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-white/40 uppercase tracking-[0.2em] mb-1">Base de Datos</p>
                                <p class="text-lg font-orbitron font-black text-white truncate">{{ info?.database || 'CALCULANDO...' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="radiant-card-stat">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-red-600/10 rounded-2xl border border-red-600/20 shadow-lg shadow-red-600/10">
                                <i class="fas fa-folder-open text-xl text-red-500"></i>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-[9px] font-black text-white/40 uppercase tracking-[0.2em] mb-1">Directorio de Almacenamiento</p>
                                <p class="text-xs font-black text-white/80 truncate uppercase tracking-tighter">{{ info?.storage_path || 'BUSCANDO...' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-black/40 backdrop-blur-3xl border border-white/10 rounded-[2rem] overflow-hidden shadow-2xl">
                    <div class="px-8 py-6 border-b border-white/5 bg-black/40 flex items-center justify-between">
                        <h3 class="text-xs font-black text-white uppercase tracking-[0.3em] flex items-center gap-3">
                            <i class="fas fa-history text-red-500"></i>
                            Historial de Respaldos
                        </h3>
                        <span class="px-4 py-1.5 bg-red-600/10 border border-red-600/20 text-red-500 text-[10px] font-black rounded-full uppercase tracking-widest">
                            {{ backups.length }} Archivos
                        </span>
                    </div>

                    <div class="py-24 text-center" *ngIf="backups.length === 0">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-white/5 rounded-full mb-6 border border-white/10">
                            <i class="fas fa-box-open text-4xl text-white/20"></i>
                        </div>
                        <p class="text-[10px] font-black text-white/40 uppercase tracking-[0.3em] mb-2">No se han encontrado respaldos en el servidor.</p>
                        <p class="text-[9px] font-black text-red-500/60 uppercase tracking-widest">Haga clic en el botón superior para crear uno nuevo.</p>
                    </div>

                    <div class="overflow-x-auto" *ngIf="backups.length > 0">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-black/40 text-white/40 text-[9px] font-black uppercase tracking-[0.3em] border-b border-white/5">
                                    <th class="py-5 px-8">Archivo</th>
                                    <th class="py-5 px-8">Fecha de Creación</th>
                                    <th class="py-5 px-8">Tamaño</th>
                                    <th class="py-5 px-8 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 uppercase">
                                <tr *ngFor="let b of backups" class="hover:bg-white/[0.03] transition-all duration-300 group">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 flex items-center justify-center bg-red-600/5 border border-red-600/10 text-red-500 rounded-xl group-hover:scale-110 transition-transform shadow-lg shadow-red-600/5">
                                                <i class="fas fa-file-code"></i>
                                            </div>
                                            <span class="font-black text-white text-xs tracking-tight">{{ b.nombre }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="text-[10px] font-black text-white/40 tracking-widest">{{ b.fecha }}</span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="px-3 py-1 bg-white/5 border border-white/10 text-white/60 text-[9px] font-black rounded-lg tracking-widest">
                                            {{ (b.size / 1024 / 1024) | number:'1.2-2' }} MB
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center justify-center gap-3">
                                            <button 
                                                (click)="descargar(b.nombre)"
                                                class="w-10 h-10 flex items-center justify-center bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 rounded-xl hover:bg-emerald-500 hover:text-white transition-all shadow-lg shadow-emerald-500/10 active:scale-90"
                                                title="Descargar"
                                            >
                                                <i class="fas fa-download text-sm"></i>
                                            </button>
                                            <button 
                                                (click)="eliminar(b.nombre)"
                                                class="w-10 h-10 flex items-center justify-center bg-red-600/10 border border-red-600/20 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-lg shadow-red-600/10 active:scale-90"
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
