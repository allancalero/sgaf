import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { SistemaService } from '../../services/sistema.service';

@Component({
    selector: 'app-auditoria',
    standalone: true,
    imports: [CommonModule, MainLayoutComponent],
    template: `
        <app-main-layout>
            <div class="p-6 lg:p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Auditoría</h2>
                    <p class="text-sm text-gray-500">Registro de actividades del sistema</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-6 py-3 text-left">Fecha</th>
                                    <th class="px-6 py-3 text-left">Usuario</th>
                                    <th class="px-6 py-3 text-left">Descripción</th>
                                    <th class="px-6 py-3 text-left">Tipo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr *ngIf="loading"><td colspan="4" class="px-6 py-8 text-center text-gray-500">Cargando...</td></tr>
                                <tr *ngIf="!loading && logs.length === 0"><td colspan="4" class="px-6 py-8 text-center text-gray-500">No hay registros de auditoría</td></tr>
                                <tr *ngFor="let log of logs" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ log.created_at | date:'dd/MM/yyyy HH:mm' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ log.usuario || 'Sistema' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ log.description }}</td>
                                    <td class="px-6 py-4 text-sm"><span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">{{ log.subject_type }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </app-main-layout>
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
