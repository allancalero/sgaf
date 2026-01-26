import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SistemaService } from '../../services/sistema.service';

@Component({
    selector: 'app-seguridad',
    standalone: true,
    imports: [CommonModule],
    template: `
            <div class="p-6 lg:p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Seguridad</h2>
                    <p class="text-sm text-gray-500">Roles y permisos del sistema</p>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Roles</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div *ngFor="let r of roles" class="px-6 py-4 flex justify-between items-center">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ r.name }}</span>
                                <span class="text-sm text-gray-500">{{ r.guard_name }}</span>
                            </div>
                            <div *ngIf="roles.length === 0" class="px-6 py-8 text-center text-gray-500">No hay roles definidos</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Permisos</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                            <div *ngFor="let p of permissions" class="px-6 py-3">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ p.name }}</span>
                            </div>
                            <div *ngIf="permissions.length === 0" class="px-6 py-8 text-center text-gray-500">No hay permisos definidos</div>
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
