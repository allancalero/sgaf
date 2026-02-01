import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';

@Component({
    selector: 'app-verificar-activo',
    standalone: true,
    imports: [CommonModule, FormsModule],
    template: `
        <div class="min-h-screen spotlight-bg flex items-center justify-center p-4">
            <div class="w-full max-w-2xl animate-fade-in">
                
                <!-- Header -->
                <div class="text-center mb-10">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-red-600/10 rounded-[2rem] mb-6 border border-red-600/20 shadow-2xl shadow-red-600/10">
                        <i class="fas fa-qrcode text-5xl text-red-500"></i>
                    </div>
                    <h1 class="text-4xl font-orbitron font-black text-white uppercase tracking-tighter leading-none">Verificar <span class="text-red-600">Activo</span></h1>
                    <p class="text-red-500/60 text-[10px] font-black uppercase tracking-[0.4em] mt-4">Sistema de Gestión de Activo Fijo</p>
                </div>

                <!-- Search Box -->
                <div class="bg-black/40 backdrop-blur-3xl rounded-[2.5rem] p-10 border border-white/10 shadow-2xl relative overflow-hidden" *ngIf="!asset">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-red-600 to-transparent"></div>
                    
                    <label class="text-[10px] font-black text-white/40 uppercase tracking-[0.3em] mb-4 block ml-1">
                        Código de Inventario Institucional
                    </label>
                    <div class="flex gap-4">
                        <input type="text" [(ngModel)]="codigoInput" 
                            placeholder="EJ: 123-004-007-000001"
                            class="flex-1 bg-white/5 border border-white/5 rounded-2xl px-6 py-5 text-white placeholder-white/20 focus:outline-none focus:ring-2 focus:ring-red-600/50 font-black tracking-widest uppercase text-sm transition-all"
                            (keyup.enter)="buscarActivo()">
                        <button (click)="buscarActivo()" [disabled]="loading"
                            class="w-16 h-16 bg-red-600 hover:bg-black text-white font-black rounded-2xl transition-all disabled:opacity-30 border border-red-600/40 shadow-lg shadow-red-600/20 active:scale-90">
                            <i class="fas" [class.fa-bolt]="!loading" [class.fa-spinner]="loading" [class.fa-spin]="loading"></i>
                        </button>
                    </div>
                    <p *ngIf="error" class="text-red-500 text-[10px] font-black uppercase tracking-widest mt-6 flex items-center gap-3 bg-red-600/10 p-4 rounded-xl border border-red-600/20">
                        <i class="fas fa-exclamation-triangle"></i> {{ error }}
                    </p>
                </div>

                <!-- Asset Card -->
                <div *ngIf="asset" class="bg-black/40 backdrop-blur-3xl rounded-[3rem] shadow-2xl overflow-hidden border border-white/10 relative">
                    <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-red-600 to-transparent"></div>
                    
                    <!-- Card Header -->
                    <div class="bg-white/[0.02] px-10 py-8 border-b border-white/5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-500 font-black text-[10px] uppercase tracking-[0.4em] mb-2">Protocolo de Verificación</p>
                                <h2 class="text-2xl font-orbitron font-black text-white uppercase tracking-tighter">{{ asset.nombre_activo }}</h2>
                            </div>
                            <div class="w-14 h-14 bg-red-600/10 border border-red-600/20 rounded-2xl flex items-center justify-center text-red-500 shadow-lg shadow-red-600/10">
                                <i class="fas fa-check-double text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Asset Info -->
                    <div class="p-10 space-y-10">
                        
                        <!-- Codigo & Estado -->
                        <div class="flex flex-wrap items-center gap-4">
                            <span class="text-xs font-black text-red-500 bg-red-600/10 px-6 py-2.5 rounded-xl border border-red-600/20 tracking-widest font-mono">
                                {{ asset.codigo_inventario }}
                            </span>
                            <div [class]="'text-[10px] font-black uppercase px-6 py-2.5 rounded-xl border tracking-[0.2em] ' + 
                                (asset.estado === 'BUENO' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 
                                asset.estado === 'REGULAR' ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 
                                'bg-red-500/10 text-red-500 border-red-500/20')">
                                {{ asset.estado }}
                            </div>
                        </div>

                        <!-- Grid Info -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-10">
                            
                            <!-- Technical -->
                            <div class="space-y-6">
                                <h3 class="text-[9px] font-black text-red-500 uppercase tracking-[0.5em] flex items-center gap-3">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Especificaciones
                                </h3>
                                <div class="space-y-5">
                                    <div>
                                        <p class="text-[9px] text-white/30 font-black uppercase tracking-widest mb-2 font-inter">Marca / Modelo</p>
                                        <p class="text-xs font-black text-white p-3 bg-white/[0.03] rounded-xl border border-white/5 uppercase">{{ asset.marca || 'S/M' }} • {{ asset.modelo || 'S/M' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] text-white/30 font-black uppercase tracking-widest mb-2 font-inter">Identificador Serial</p>
                                        <p class="text-xs font-black text-white p-3 bg-white/[0.03] rounded-xl border border-white/5 uppercase">{{ asset.serie || 'S/N' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Assignment -->
                            <div class="space-y-6">
                                <h3 class="text-[9px] font-black text-red-500 uppercase tracking-[0.5em] flex items-center gap-3">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Despliegue Actual
                                </h3>
                                <div class="space-y-5">
                                    <div>
                                        <p class="text-[9px] text-white/30 font-black uppercase tracking-widest mb-2 font-inter">Área / Departamento</p>
                                        <p class="text-xs font-black text-white p-3 bg-white/[0.03] rounded-xl border border-white/5 uppercase">{{ asset.area?.nombre || 'S/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] text-white/30 font-black uppercase tracking-widest mb-2 font-inter">Custodio Final</p>
                                        <p class="text-xs font-black text-white p-3 bg-white/[0.03] rounded-xl border border-white/5 uppercase">
                                            {{ asset.personal ? (asset.personal.nombre + ' ' + (asset.personal.apellido || '')) : 'S/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial -->
                        <div class="bg-black/40 border border-white/5 rounded-[2.5rem] p-8 space-y-6 relative overflow-hidden">
                            <div class="absolute inset-0 bg-red-600/[0.02] blur-3xl"></div>
                            <h3 class="text-[9px] font-black text-red-500 uppercase tracking-[0.5em] flex items-center gap-3 relative z-10">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Datos Financieros
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 relative z-10">
                                <div>
                                    <p class="text-[9px] text-white/30 font-black uppercase tracking-widest mb-2">Valor Contable</p>
                                    <p class="text-xl font-black text-white">C$ {{ asset.precio_adquisicion | number:'1.2-2' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-white/30 font-black uppercase tracking-widest mb-2">Alta en Sistema</p>
                                    <p class="text-xs font-black text-red-500/80 tracking-widest uppercase">{{ asset.fecha_adquisicion | date:'dd.MM.yyyy' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-white/30 font-black uppercase tracking-widest mb-2">Origen / Fuente</p>
                                    <p class="text-[10px] font-bold text-white uppercase truncate">{{ asset.fuente_financiamiento?.nombre || 'S/F' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-10 py-8 bg-white/[0.02] border-t border-white/5 flex flex-col sm:flex-row justify-between items-center gap-6">
                        <p class="text-[9px] text-white/30 font-black uppercase tracking-[0.4em]">SIGAF PRO // VERIFICACIÓN</p>
                        <button (click)="nuevaBusqueda()" class="w-full sm:w-auto px-8 py-3 bg-red-600 hover:bg-black text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all border border-red-600/40 active:scale-95 flex items-center justify-center gap-3">
                            <i class="fas fa-search"></i> Protocolo de Re-búsqueda
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <p class="text-center text-white/20 text-[9px] font-black uppercase tracking-[0.5em] mt-12">
                    Sistema de Gestión de Activo Fijo // Alcaldía de Tipitapa
                </p>
            </div>
        </div>
    `,
    styles: [`
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.4s ease-out;
        }
    `]
})
export class VerificarActivoComponent implements OnInit {
    codigoInput: string = '';
    asset: any = null;
    loading: boolean = false;
    error: string = '';

    constructor(
        private route: ActivatedRoute,
        private http: HttpClient
    ) { }

    ngOnInit() {
        // Check if code is passed as route parameter
        this.route.params.subscribe(params => {
            if (params['codigo']) {
                this.codigoInput = params['codigo'];
                this.buscarActivo();
            }
        });

        // Also check query params
        this.route.queryParams.subscribe(params => {
            if (params['codigo']) {
                this.codigoInput = params['codigo'];
                this.buscarActivo();
            }
        });
    }

    buscarActivo() {
        if (!this.codigoInput.trim()) {
            this.error = 'Ingrese un código de inventario';
            return;
        }

        this.loading = true;
        this.error = '';
        this.asset = null;

        this.http.get<any>(`${environment.apiUrl} /assets/verify / ${encodeURIComponent(this.codigoInput.trim())} `)
            .subscribe({
                next: (data) => {
                    this.loading = false;
                    if (data) {
                        this.asset = data;
                    } else {
                        this.error = 'Activo no encontrado';
                    }
                },
                error: (err) => {
                    this.loading = false;
                    if (err.status === 404) {
                        this.error = 'Activo no encontrado con ese código';
                    } else {
                        this.error = 'Error al buscar el activo';
                    }
                    console.error(err);
                }
            });
    }

    nuevaBusqueda() {
        this.asset = null;
        this.codigoInput = '';
        this.error = '';
    }
}
