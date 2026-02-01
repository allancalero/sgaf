import { Component, OnInit, HostListener, ElementRef, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router, NavigationEnd } from '@angular/router';
import { filter, debounceTime, distinctUntilChanged, switchMap, tap } from 'rxjs/operators';
import { Subject, of } from 'rxjs';
import { AuthService } from '../../services/auth.service';
import { ThemeService } from '../../services/theme.service';
import { AssetService } from '../../services/asset.service';
import { InactivityService } from '../../services/inactivity.service';
import { InactivityModalComponent } from '../../components/inactivity-modal/inactivity-modal.component';

interface MenuItem {
    label: string;
    icon: string;
    route?: string;
    children?: MenuItem[];
    expanded?: boolean;
}

@Component({
    selector: 'app-main-layout',
    standalone: true,
    imports: [CommonModule, RouterModule, FormsModule, InactivityModalComponent],
    templateUrl: './main-layout.component.html',
    styleUrls: ['./main-layout.component.css']
})
export class MainLayoutComponent implements OnInit {
    sidebarOpen = true;
    user: any;
    today = new Date();
    showScrollTop = false;
    isLogin: boolean = true; // Start as true to show content while router loads

    // Intelligent Search
    searchTerm: string = '';
    searchResults: any[] = [];
    isSearching: boolean = false;
    showResults: boolean = false;
    private searchSubject = new Subject<string>();

    // Asset Detail Modal
    selectedAsset: any = null;
    showAssetModal: boolean = false;
    isLoadingAsset: boolean = false;
    selectedAssetQrUrl: string | null = null;

    @ViewChild('mainContent') mainContent!: ElementRef;

    menuItems: MenuItem[] = [
        {
            label: 'Panel de Control',
            icon: 'dashboard',
            route: '/dashboard'
        },
        {
            label: 'Catálogos',
            icon: 'folder',
            expanded: false,
            children: [
                { label: 'Ubicación / Areas', icon: 'location', route: '/catalogos/ubicacion' },
                { label: 'Recursos Humanos', icon: 'users', route: '/catalogos/recursos-humanos' },
                { label: 'Catálogos de Activos', icon: 'box', route: '/catalogos/activos-fijo' }
            ]
        },
        {
            label: 'Activos Fijo',
            icon: 'box',
            expanded: true,
            children: [
                { label: 'Activos', icon: 'box', route: '/activos' },
                { label: 'En Desuso', icon: 'pause', route: '/activos/desuso' },
                { label: 'De Baja', icon: 'trash', route: '/activos/baja' },
                { label: 'Reasignaciones', icon: 'transfer', route: '/activos/reasignaciones' },

                { label: 'Reportes', icon: 'report', route: '/activos/reportes' },
                { label: 'Depreciación', icon: 'chart', route: '/activos/depreciacion' },
                { label: 'Trazabilidad', icon: 'history', route: '/activos/trazabilidad' },
                { label: 'Etiquetas QR', icon: 'qr', route: '/activos/etiquetas-qr' }
            ]
        },
        {
            label: 'Sistema',
            icon: 'settings',
            expanded: false,
            children: [
                { label: 'Usuarios', icon: 'users', route: '/sistema/usuarios' },
                { label: 'Respaldo', icon: 'backup', route: '/sistema/respaldo' },
                { label: 'Seguridad', icon: 'shield', route: '/sistema/seguridad' },
                { label: 'Auditoría', icon: 'clipboard', route: '/sistema/auditoria' }
            ]
        }
    ];

    constructor(
        private authService: AuthService,
        public themeService: ThemeService,
        private assetService: AssetService,
        private inactivityService: InactivityService,
        private router: Router
    ) {
        // Set initial state based on current URL
        this.isLogin = this.router.url.includes('/login') || this.router.url === '/';
        console.log('MainLayout Constructor - Initial URL:', this.router.url, 'isLogin:', this.isLogin);

        // Subscribe to route changes
        this.router.events.pipe(
            filter(event => event instanceof NavigationEnd)
        ).subscribe((event: any) => {
            this.isLogin = event.urlAfterRedirects.includes('/login');
            console.log('MainLayout NavigationEnd - URL:', event.urlAfterRedirects, 'isLogin:', this.isLogin);
        });

        this.authService.currentUser.subscribe(user => {
            this.user = user;
        });

        // Setup intelligent search with debounce
        this.searchSubject.pipe(
            debounceTime(300),
            distinctUntilChanged(),
            switchMap(term => {
                if (term.length < 2) {
                    this.searchResults = [];
                    this.isSearching = false;
                    return of([]);
                }
                this.isSearching = true;
                return this.assetService.quickSearch(term).pipe(
                    tap(res => console.log('QuickSearch API Response:', res)),
                    tap(() => this.isSearching = false)
                );
            })
        ).subscribe({
            next: (results) => {
                console.log('Search results success:', results);
                this.searchResults = results;
                this.isSearching = false;
                this.showResults = true;
            },
            error: (err) => {
                console.error('Search observable error:', err);
                this.isSearching = false;
                this.searchResults = [];
            }
        });

        // Auto-expand menu based on current route
        this.router.events.pipe(
            filter(event => event instanceof NavigationEnd)
        ).subscribe(() => {
            if (this.isLogin) {
                this.inactivityService.stopMonitoring();
            } else {
                this.inactivityService.startMonitoring();
            }
            this.checkActiveMenu();
        });
    }

    ngOnInit() {
        this.checkActiveMenu();
        if (!this.isLoginPage()) {
            this.inactivityService.startMonitoring();
        }
    }

    checkActiveMenu() {
        const currentUrl = this.router.url;
        this.menuItems.forEach(item => {
            if (item.children) {
                // Check if any child matches the current URL
                const hasActiveChild = item.children.some(child =>
                    child.route && currentUrl.startsWith(child.route)
                );
                if (hasActiveChild) {
                    item.expanded = true;
                }
            }
        });
    }

    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
    }

    closeSidebarMobile() {
        if (window.innerWidth < 1024) {
            this.sidebarOpen = false;
        }
    }



    logout() {
        this.authService.logout();
    }

    isLoginPage(): boolean {
        return this.router.url.includes('/login');
    }

    // Intelligent Search Methods
    onSearchInput() {
        this.showResults = true;
        console.log('Search input changed:', this.searchTerm);
        this.searchSubject.next(this.searchTerm);
    }

    selectResult(asset: any) {
        this.searchTerm = '';
        this.searchResults = [];
        this.showResults = false;

        // Open modal IMMEDIATELY with available data from quick-search
        this.selectedAsset = { ...asset };
        this.showAssetModal = true;
        this.isLoadingAsset = false;
        this.selectedAssetQrUrl = null;

        // Load QR code
        this.assetService.getQrBlob(asset.id).subscribe({
            next: (blob) => {
                this.selectedAssetQrUrl = URL.createObjectURL(blob);
            },
            error: (err) => console.error('Error loading QR', err)
        });

        // Background fetch for the complete Model data
        this.assetService.getAsset(asset.id).subscribe({
            next: (data) => {
                // Merge data, enriching what we already have
                this.selectedAsset = { ...this.selectedAsset, ...data };
            },
            error: (err) => {
                console.error('Error in background asset loading:', err);
                // We don't close the modal as we still have the basic data
            }
        });
    }

    closeAssetModal(event?: Event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        this.showAssetModal = false;
        this.selectedAsset = null;
        if (this.selectedAssetQrUrl) {
            URL.revokeObjectURL(this.selectedAssetQrUrl);
            this.selectedAssetQrUrl = null;
        }
    }

    goToAssetList(event?: Event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        if (this.selectedAsset) {
            this.router.navigate(['/activos'], {
                queryParams: { search: this.selectedAsset.codigo_inventario }
            });
            this.closeAssetModal();
        }
    }

    @HostListener('document:click', ['$event'])
    onClickOutside(event: Event) {
        if (!this.showResults) return;

        const target = event.target as HTMLElement;
        const searchContainer = document.querySelector('.search-container');

        if (searchContainer && !searchContainer.contains(target)) {
            this.showResults = false;
        }
    }

    onMainScroll(event: Event) {
        const element = event.target as HTMLElement;
        this.showScrollTop = element.scrollTop > 300;
    }

    scrollToTop() {
        if (this.mainContent && this.mainContent.nativeElement) {
            const mainElement = this.mainContent.nativeElement as HTMLElement;
            const startPosition = mainElement.scrollTop;
            const duration = 500;
            const startTime = performance.now();

            const animateScroll = (currentTime: number) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function (easeOutCubic)
                const easeOut = 1 - Math.pow(1 - progress, 3);

                mainElement.scrollTop = startPosition * (1 - easeOut);

                if (progress < 1) {
                    requestAnimationFrame(animateScroll);
                }
            };

            requestAnimationFrame(animateScroll);
        }
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
            clipboard: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
            folder: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
            settings: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            pause: 'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z',
            trash: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'
        };
        return icons[iconName] || icons['box'];
    }
}
