import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { catchError, throwError } from 'rxjs';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
    const token = localStorage.getItem('token');

    // Add headers for API requests
    let headers: { [key: string]: string } = {
        'Accept': 'application/json'
    };

    // Only add Content-Type for non-GET requests
    if (req.method !== 'GET') {
        headers['Content-Type'] = 'application/json';
    }

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const cloned = req.clone({
        setHeaders: headers
    });

    console.log('AuthInterceptor: Request to', req.url, 'with token:', token ? 'Present' : 'Missing');

    return next(cloned).pipe(
        catchError((error: HttpErrorResponse) => {
            console.error('AuthInterceptor: Error response:', error.status, error.message);
            if (error.status === 401) {
                // Token expired or invalid
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/SGAF2/login';
            }
            return throwError(() => error);
        })
    );
};
