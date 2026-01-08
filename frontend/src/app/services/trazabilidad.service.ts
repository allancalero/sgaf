import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class TrazabilidadService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    getHistorial(search = '', page = 1): Observable<any> {
        let params = new HttpParams().set('page', page.toString());
        if (search) params = params.set('search', search.toString());
        return this.http.get(`${this.apiUrl}/trazabilidad`, { params });
    }

    buscarActivo(search: string): Observable<any[]> {
        return this.http.get<any[]>(`${this.apiUrl}/trazabilidad/buscar-activo`, { params: { search } });
    }
}
