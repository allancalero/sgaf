import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { ThemeService } from '../../services/theme.service';

interface MenuItem {
    label: string;
    icon: string;
    route?: string;
}

interface MenuSection {
    title: string;
    items: MenuItem[];
}

@Component({
    selector: 'app-main-layout',
    standalone: true,
    imports: [CommonModule, RouterModule],
    templateUrl: './main-layout.component.html',
    styleUrls: ['./main-layout.component.css']
})
export class MainLayoutComponent {
    sidebarOpen = true;
    user: any;
    today = new Date();

    menuSections: MenuSection[] = [
        {
            title: 'MONITOREO',
            items: [
                { label: 'Dashboard', icon: 'dashboard', route: '/dashboard' }
            ]
        },
        {
            title: 'CATÁLOGOS',
            items: [
                { label: 'Ubicación / Areas', icon: 'location', route: '/catalogos/ubicacion' },
                { label: 'Recursos Humanos', icon: 'users', route: '/catalogos/recursos-humanos' },
                { label: 'Catálogos de Activos', icon: 'box', route: '/catalogos/activos-fijo' }
            ]
        },
        {
            title: 'ACTIVOS FIJO',
            items: [
                { label: 'Activos', icon: 'box', route: '/activos-fijos' },
                { label: 'Mis Activos', icon: 'user', route: '/activos/mis-activos' },
                { label: 'Reasignaciones', icon: 'transfer', route: '/activos/reasignaciones' },
                { label: 'Reportes', icon: 'report', route: '/activos/reportes' },
                { label: 'Depreciación', icon: 'chart', route: '/activos/depreciacion' },
                { label: 'Trazabilidad', icon: 'history', route: '/activos/trazabilidad' },
                { label: 'Etiquetas QR', icon: 'qr', route: '/activos/etiquetas-qr' }
            ]
        },
        {
            title: 'SISTEMA',
            items: [
                { label: 'Usuarios', icon: 'users', route: '/sistema/usuarios' },
                { label: 'Respaldo', icon: 'backup', route: '/sistema/respaldo' },
                { label: 'Seguridad', icon: 'shield', route: '/sistema/seguridad' },
                { label: 'Auditoría', icon: 'clipboard', route: '/sistema/auditoria' }
            ]
        }
    ];

    constructor(
        private authService: AuthService,
        public themeService: ThemeService
    ) {
        this.authService.currentUser.subscribe(user => {
            this.user = user;
        });
    }

    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
    }

    toggleTheme() {
        this.themeService.toggleTheme();
    }

    logout() {
        this.authService.logout();
    }

    getIcon(iconName: string): string {
        const icons: { [key: string]: string } = {
            dashboard: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            location: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
            users: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            box: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            user: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            transfer: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
            report: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            chart: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            history: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            qr: 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 17h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
            backup: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
            shield: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            clipboard: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'
        };
        return icons[iconName] || icons['box'];
    }
}
