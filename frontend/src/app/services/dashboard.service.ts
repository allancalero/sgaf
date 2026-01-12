import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { DashboardStats } from '../models/dashboard-stats';

@Injectable({
    providedIn: 'root'
})
export class DashboardService {
    private apiUrl: string;

    constructor(private http: HttpClient) {
        const path = window.location.pathname;
        let baseApi = environment.apiUrl;
        if (path.includes('SGAF2')) {
            baseApi = path.split('SGAF2')[0] + 'api';
        }
        this.apiUrl = `${baseApi}/dashboard`;
    }

    getStats(): Observable<DashboardStats> {
        return this.http.get<DashboardStats>(this.apiUrl);
    }
}
