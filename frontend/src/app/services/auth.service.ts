import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { BehaviorSubject, Observable, tap, map, catchError, of } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
    providedIn: 'root'
})
export class AuthService {
    private apiUrl = environment.apiUrl;
    private currentUserSubject = new BehaviorSubject<any>(null);
    public currentUser = this.currentUserSubject.asObservable();

    constructor(private http: HttpClient, private router: Router) {
        // Constructor no longer auto-checks. We wait for APP_INITIALIZER.
    }

    login(credentials: any): Observable<any> {
        return this.http.post(`${this.apiUrl}/login`, credentials).pipe(
            tap((response: any) => {
                if (response.user) {
                    // Token is handled by HttpOnly cookie, just store user info
                    localStorage.setItem('user', JSON.stringify(response.user));
                    this.currentUserSubject.next(response.user);
                }
            })
        );
    }

    logout() {
        // Clear local state immediately to avoid UI lag/loops
        localStorage.removeItem('user');
        this.currentUserSubject.next(null);
        this.router.navigate(['/login']);

        // Notify backend (fire and forget)
        this.http.post(`${this.apiUrl}/logout`, {}).subscribe({
            next: () => console.log('Backend logout success'),
            error: (err) => console.warn('Backend logout failed or token already invalid', err)
        });
    }

    checkAuthStatus(): Observable<boolean> {
        return this.http.get(`${this.apiUrl}/user`).pipe(
            tap((user: any) => {
                localStorage.setItem('user', JSON.stringify(user));
                this.currentUserSubject.next(user);
            }),
            map(() => true),
            catchError(() => {
                localStorage.removeItem('user');
                this.currentUserSubject.next(null);
                return of(false);
            })
        );
    }

    getToken() {
        // Tokens are now HttpOnly cookies, not accessible via JS
        return null;
    }

    isLoggedIn() {
        return !!this.currentUserSubject.value;
    }
}
