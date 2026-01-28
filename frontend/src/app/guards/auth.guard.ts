import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const authGuard = (route: any) => {
    const authService = inject(AuthService);
    const router = inject(Router);

    if (authService.isLoggedIn()) {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        const isChangePasswordRoute = route?.routeConfig?.path === 'change-password';

        if (user.must_change_password) {
            // If user is already on login page (which we can't easily check here without circular dep or route snapshot check),
            // we should let the component handle it.
            // But AuthGuard usually protects internal routes.
            // If user tries to go to /dashboard, send them to /login
            router.navigate(['/login']);
            return false;
        }

        return true;
    }

    router.navigate(['/login']);
    return false;
};
