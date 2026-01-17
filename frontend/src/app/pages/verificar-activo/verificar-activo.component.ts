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
        <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 flex items-center justify-center p-4">
            <div class="w-full max-w-2xl">
                
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-500/20 rounded-2xl mb-4">
                        <i class="fas fa-qrcode text-4xl text-blue-400"></i>
                    </div>
                    <h1 class="text-3xl font-black text-white uppercase tracking-tight">Verificar Activo</h1>
                    <p class="text-blue-300/70 text-sm mt-2">Sistema de Gestión de Activos Fijos</p>
                </div>

                <!-- Search Box -->
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20 mb-6" *ngIf="!asset">
                    <label class="text-xs font-bold text-blue-300 uppercase tracking-widest mb-2 block">
                        Ingrese el Código de Inventario
                    </label>
                    <div class="flex gap-3">
                        <input type="text" [(ngModel)]="codigoInput" 
                            placeholder="Ej: 123-004-007-000001"
                            class="flex-1 bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                            (keyup.enter)="buscarActivo()">
                        <button (click)="buscarActivo()" [disabled]="loading"
                            class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl transition-all disabled:opacity-50">
                            <i class="fas" [class.fa-search]="!loading" [class.fa-spinner]="loading" [class.fa-spin]="loading"></i>
                        </button>
                    </div>
                    <p *ngIf="error" class="text-red-400 text-sm mt-3 flex items-center gap-2">
                        <i class="fas fa-exclamation-circle"></i> {{ error }}
                    </p>
                </div>

                <!-- Asset Card -->
                <div *ngIf="asset" class="bg-white dark:bg-[#111827] rounded-2xl shadow-2xl overflow-hidden border dark:border-white/10 animate-fade-in">
                    
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-xs font-bold uppercase tracking-widest mb-1">Activo Verificado</p>
                                <h2 class="text-xl font-black text-white uppercase">{{ asset.nombre_activo }}</h2>
                            </div>
                            <div class="bg-white/20 rounded-xl p-3">
                                <i class="fas fa-check-circle text-2xl text-white"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Asset Info -->
                    <div class="p-6 space-y-6">
                        
                        <!-- Codigo & Estado -->
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-sm font-black text-blue-600 bg-blue-500/10 px-4 py-2 rounded-lg ring-1 ring-blue-500/30 font-mono">
                                {{ asset.codigo_inventario }}
                            </span>
                            <div [class]="'text-xs font-black uppercase px-3 py-1.5 rounded-lg ring-1 ' + 
                                (asset.estado === 'BUENO' ? 'bg-emerald-500/10 text-emerald-600 ring-emerald-500/30' : 
                                asset.estado === 'REGULAR' ? 'bg-orange-500/10 text-orange-600 ring-orange-500/30' : 
                                'bg-red-500/10 text-red-600 ring-red-500/30')">
                                {{ asset.estado }}
                            </div>
                        </div>

                        <!-- Grid Info -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            
                            <!-- Technical -->
                            <div class="space-y-4">
                                <h3 class="text-[10px] font-black text-blue-500 uppercase tracking-widest flex items-center gap-2">
                                    <i class="fas fa-info-circle"></i> Información Técnica
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase">Marca / Modelo</p>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ asset.marca || 'N/A' }} • {{ asset.modelo || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase">Número de Serie</p>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ asset.serie || 'SIN SERIE' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase">Clasificación</p>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ asset.clasificacion?.nombre || 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Assignment -->
                            <div class="space-y-4">
                                <h3 class="text-[10px] font-black text-blue-500 uppercase tracking-widest flex items-center gap-2">
                                    <i class="fas fa-user-check"></i> Asignación Actual
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase">Área</p>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ asset.area?.nombre || 'NO ASIGNADO' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase">Responsable</p>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                            {{ asset.personal ? (asset.personal.nombre + ' ' + (asset.personal.apellido || '')) : 'SIN ASIGNAR' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase">Ubicación</p>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ asset.ubicacion?.nombre || 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial -->
                        <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 space-y-3">
                            <h3 class="text-[10px] font-black text-blue-500 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-dollar-sign"></i> Datos Financieros
                            </h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Valor Contable</p>
                                    <p class="text-lg font-black text-gray-900 dark:text-white">C$ {{ asset.precio_adquisicion | number:'1.2-2' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Fecha Adquisición</p>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ asset.fecha_adquisicion | date:'dd/MM/yyyy' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Fuente Fondos</p>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ asset.fuente_financiamiento?.nombre || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-white/5 border-t dark:border-white/10 flex justify-between items-center">
                        <p class="text-[9px] text-gray-400 font-bold uppercase">SGAF V2 • Verificación de Activo</p>
                        <button (click)="nuevaBusqueda()" class="text-blue-500 hover:text-blue-600 text-sm font-bold flex items-center gap-2">
                            <i class="fas fa-search"></i> Nueva Búsqueda
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <p class="text-center text-white/40 text-xs mt-8">
                    Sistema de Gestión de Activos Fijos • Alcaldía Municipal
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

        this.http.get<any>(`${environment.apiUrl}/assets/verify/${encodeURIComponent(this.codigoInput.trim())}`)
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
