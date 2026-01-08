import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class SistemaService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    getAuditoria(page = 1, search = ''): Observable<any> {
        let params = new HttpParams().set('page', page);
        if (search) params = params.set('search', search);
        return this.http.get(`${this.apiUrl}/sistema/auditoria`, { params });
    }

    getRespaldo(): Observable<any> {
        return this.http.get(`${this.apiUrl}/sistema/respaldo`);
    }

    getSeguridad(): Observable<any> {
        return this.http.get(`${this.apiUrl}/sistema/seguridad`);
    }
}
