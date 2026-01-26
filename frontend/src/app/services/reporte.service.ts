import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class ReporteService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    descargarReporte(reportId: string, filters?: { area_id?: number, clasificacion_id?: number, ubicacion_id?: number, personal_id?: number, estado?: string }): Observable<Blob> {
        let url = `${this.apiUrl}/reportes/${reportId}/download`;
        if (filters) {
            const params = new URLSearchParams();
            if (filters.area_id) params.append('area_id', filters.area_id.toString());
            if (filters.clasificacion_id) params.append('clasificacion_id', filters.clasificacion_id.toString());
            if (filters.ubicacion_id) params.append('ubicacion_id', filters.ubicacion_id.toString());
            if (filters.personal_id) params.append('personal_id', filters.personal_id.toString());
            if (filters.estado) params.append('estado', filters.estado);
            const queryString = params.toString();
            if (queryString) url += '?' + queryString;
        }
        return this.http.get(url, {
            responseType: 'blob'
        });
    }

    getResumen(): Observable<any> {
        return this.http.get(`${this.apiUrl}/reportes/resumen`);
    }
}
