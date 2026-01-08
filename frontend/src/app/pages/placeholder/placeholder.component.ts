import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';

@Component({
    selector: 'app-placeholder',
    standalone: true,
    imports: [CommonModule, MainLayoutComponent],
    template: `
        <app-main-layout>
            <div class="p-6 lg:p-8">
                <div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
                    <div class="w-24 h-24 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">{{ title }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mb-4">{{ description }}</p>
                    <div class="px-4 py-2 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-lg text-sm font-medium">
                        🚧 Módulo en desarrollo - Próximamente disponible
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-6">
                        Este módulo está siendo migrado desde la versión Vue.
                    </p>
                </div>
            </div>
        </app-main-layout>
    `
})
export class PlaceholderComponent {
    title = 'Próximamente';
    description = 'Este módulo está siendo migrado y estará disponible pronto.';

    constructor(private route: ActivatedRoute) {
        this.route.data.subscribe(data => {
            if (data['title']) this.title = data['title'];
            if (data['desc']) this.description = data['desc'];
        });
    }
}
