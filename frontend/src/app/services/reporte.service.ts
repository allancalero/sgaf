import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class ReporteService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    descargarReporte(reportId: string): Observable<Blob> {
        return this.http.get(`${this.apiUrl}/reportes/${reportId}/download`, {
            responseType: 'blob'
        });
    }

    getResumen(): Observable<any> {
        return this.http.get(`${this.apiUrl}/reportes/resumen`);
    }
}
