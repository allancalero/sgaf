import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SistemaService } from '../../services/sistema.service';

@Component({
    selector: 'app-auditoria',
    standalone: true,
    imports: [CommonModule],
    template: `
            <div class="p-6 lg:p-12 animate-fade-in">
                <!-- Header Section -->
                <div class="mb-12 text-center lg:text-left">
                    <h1 class="text-5xl font-orbitron font-black text-white tracking-tighter uppercase leading-none">
                        Auditoría <span class="text-red-600">del Sistema</span>
                    </h1>
                    <p class="text-red-500/80 mt-4 text-sm font-black uppercase tracking-[0.3em]">
                        Registro cronológico de actividades y eventos institucionales.
                    </p>
                </div>

                <!-- Main Log Display -->
                <div class="bg-black/40 backdrop-blur-3xl border border-white/10 rounded-[2rem] overflow-hidden shadow-2xl relative">
                    <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-red-600/50 to-transparent"></div>
                    
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-black/60 text-white/40 text-[10px] font-black uppercase tracking-[0.4em] border-b border-white/5">
                                    <th class="py-6 px-10">Timestamp</th>
                                    <th class="py-6 px-10">Operador</th>
                                    <th class="py-6 px-10">Detalle de Actividad</th>
                                    <th class="py-6 px-10 text-right">Categoría</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5 uppercase">
                                <tr *ngIf="loading">
                                    <td colspan="4" class="py-32 text-center">
                                        <div class="flex flex-col items-center gap-8">
                                            <div class="w-16 h-16 border-t-2 border-red-600 rounded-full animate-spin"></div>
                                            <p class="text-[10px] font-black text-red-500/60 uppercase tracking-[0.6em] animate-pulse">Sincronizando bitácora...</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr *ngIf="!loading && logs.length === 0">
                                    <td colspan="4" class="py-32 text-center">
                                        <div class="opacity-20 mb-6">
                                            <i class="fas fa-history text-6xl text-white"></i>
                                        </div>
                                        <p class="text-[10px] font-black text-white/40 uppercase tracking-[0.4em]">No se han detectado registros de auditoría</p>
                                    </td>
                                </tr>
                                <tr *ngFor="let log of logs" class="hover:bg-white/[0.03] transition-all duration-300 group">
                                    <td class="px-10 py-8 text-[11px] font-black text-white/60 tracking-widest font-mono">
                                        {{ log.created_at | date:'dd/MM/yyyy HH:mm:ss' }}
                                    </td>
                                    <td class="px-10 py-8">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-red-600/10 border border-red-600/20 flex items-center justify-center text-[10px] text-red-500 font-black">
                                                {{ (log.usuario || 'SYS').substring(0,2) }}
                                            </div>
                                            <span class="text-white font-black text-xs tracking-tight">{{ log.usuario || 'SISTEMA' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8">
                                        <p class="text-xs font-bold text-white/80 tracking-tight leading-relaxed max-w-xl group-hover:text-white transition-colors">{{ log.description }}</p>
                                    </td>
                                    <td class="px-10 py-8 text-right">
                                        <span class="px-4 py-1.5 bg-red-600/10 border border-red-600/20 rounded-xl text-red-500 font-black text-[9px] tracking-[0.2em]">
                                            {{ log.subject_type }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    `
})
export class AuditoriaComponent implements OnInit {
    logs: any[] = [];
    loading = true;

    constructor(private sistemaService: SistemaService) { }

    ngOnInit() {
        this.sistemaService.getAuditoria().subscribe(res => {
            this.logs = res.data || [];
            this.loading = false;
        });
    }
}
