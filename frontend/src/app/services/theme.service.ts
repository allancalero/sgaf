import { Injectable, signal } from '@angular/core';

@Injectable({
    providedIn: 'root'
})
export class ThemeService {
    isDarkMode = signal<boolean>(true);

    constructor() {
        this.applyTheme(true);
    }

    toggleTheme() {
        // Theme is now locked to Radiant Dark
    }

    private applyTheme(isDark: boolean) {
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}
