import { Injectable, signal } from '@angular/core';

@Injectable({
    providedIn: 'root'
})
export class ThemeService {
    private darkThemeKey = 'dark-theme-enabled';
    isDarkMode = signal<boolean>(this.getInitialTheme());

    constructor() {
        this.applyTheme(this.isDarkMode());
    }

    toggleTheme() {
        const newValue = !this.isDarkMode();
        this.isDarkMode.set(newValue);
        this.applyTheme(newValue);
        localStorage.setItem(this.darkThemeKey, JSON.stringify(newValue));
    }

    private getInitialTheme(): boolean {
        const saved = localStorage.getItem(this.darkThemeKey);
        if (saved !== null) {
            return JSON.parse(saved);
        }
        // Default to dark mode as per original design
        return true;
    }

    private applyTheme(isDark: boolean) {
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}
