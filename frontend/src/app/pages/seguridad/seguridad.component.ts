import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SistemaService } from '../../services/sistema.service';

@Component({
    selector: 'app-seguridad',
    standalone: true,
    imports: [CommonModule],
    template: `
            <div class="p-6 lg:p-10 min-h-screen">
                <div class="mb-10 animate-fade-in">
                    <h1 class="text-5xl font-orbitron font-black text-white tracking-tighter uppercase leading-none">
                        Seguridad <span class="text-red-600">y Accesos</span>
                    </h1>
                    <p class="text-red-500/80 mt-3 text-sm font-black uppercase tracking-[0.3em]">
                        Gestión de roles y permisos del activo fijo institucional.
                    </p>
                </div>

                <div class="grid gap-10 lg:grid-cols-2">
                    <!-- Roles Section -->
                    <div class="bg-black/40 backdrop-blur-3xl border border-white/10 rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col">
                        <div class="px-8 py-6 border-b border-white/5 bg-black/40">
                            <h3 class="text-xs font-black text-white uppercase tracking-[0.3em] flex items-center gap-3">
                                <i class="fas fa-user-shield text-red-500"></i>
                                Roles Definidos
                            </h3>
                        </div>
                        <div class="divide-y divide-white/5 flex-1">
                            <div *ngFor="let r of roles" class="px-8 py-6 flex justify-between items-center hover:bg-white/[0.03] transition-colors group">
                                <span class="text-white font-black uppercase text-sm tracking-tight group-hover:text-red-500 transition-colors">{{ r.name }}</span>
                                <span class="px-3 py-1 bg-white/5 border border-white/10 text-white/40 text-[9px] font-black rounded-lg uppercase tracking-widest">{{ r.guard_name }}</span>
                            </div>
                            <div *ngIf="roles.length === 0" class="py-20 text-center flex flex-col items-center">
                                <i class="fas fa-users-slash text-4xl text-white/10 mb-4"></i>
                                <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">No hay roles definidos</p>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div class="bg-black/40 backdrop-blur-3xl border border-white/10 rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col">
                        <div class="px-8 py-6 border-b border-white/5 bg-black/40">
                            <h3 class="text-xs font-black text-white uppercase tracking-[0.3em] flex items-center gap-3">
                                <i class="fas fa-key text-red-500"></i>
                                Permisos del Sistema
                            </h3>
                        </div>
                        <div class="divide-y divide-white/5 max-h-[500px] overflow-y-auto flex-1">
                            <div *ngFor="let p of permissions" class="px-8 py-4 flex items-center gap-4 hover:bg-white/[0.03] transition-colors group">
                                <div class="w-1.5 h-1.5 rounded-full bg-red-600 shadow-lg shadow-red-600/40"></div>
                                <span class="text-xs font-bold text-white/60 group-hover:text-white transition-colors uppercase tracking-tight">{{ p.name }}</span>
                            </div>
                            <div *ngIf="permissions.length === 0" class="py-20 text-center flex flex-col items-center">
                                <i class="fas fa-shield-alt text-4xl text-white/10 mb-4"></i>
                                <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">No hay permisos definidos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    `
})
export class SeguridadComponent implements OnInit {
    roles: any[] = [];
    permissions: any[] = [];

    constructor(private sistemaService: SistemaService) { }

    ngOnInit() {
        this.sistemaService.getSeguridad().subscribe({
            next: (res) => {
                console.log('Seguridad Data:', res);
                this.roles = res.roles || [];
                this.permissions = res.permissions || [];
            },
            error: (err) => {
                console.error('Seguridad Error:', err);
            }
        });
    }
}
