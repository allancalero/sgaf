import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class DepreciacionService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    getDepreciacion(page = 1, search = ''): Observable<any> {
        let params = new HttpParams().set('page', page.toString());
        if (search) params = params.set('search', search);
        return this.http.get(`${this.apiUrl}/depreciacion`, { params });
    }

    getSinConfigurar(page = 1, search = ''): Observable<any> {
        let params = new HttpParams().set('page', page.toString());
        if (search) params = params.set('search', search);
        return this.http.get(`${this.apiUrl}/depreciacion/sin-configurar`, { params });
    }

    getCatalogo(): Observable<any[]> {
        return this.http.get<any[]>(`${this.apiUrl}/depreciacion/catalogo`);
    }

    configurar(id: number, data: any): Observable<any> {
        return this.http.post(`${this.apiUrl}/depreciacion/${id}/configurar`, data);
    }

    calcular(id: number): Observable<any> {
        return this.http.post(`${this.apiUrl}/depreciacion/${id}/calcular`, {});
    }

    resetear(): Observable<any> {
        return this.http.post(`${this.apiUrl}/depreciacion/reset`, {});
    }

    limpiarTodo(): Observable<any> {
        return this.http.post(`${this.apiUrl}/depreciacion/limpiar-todo`, {});
    }

    procesarMasivo(fechaCorte?: string): Observable<any> {
        const body = fechaCorte ? { fecha_corte: fechaCorte } : {};
        return this.http.post(`${this.apiUrl}/depreciacion/procesar`, body);
    }
}
