import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Area {
    id: number;
    nombre: string;
    ubicacion_id?: number | null;
    ubicacion_nombre?: string;
    estado: string;
}

export interface Ubicacion {
    id: number;
    nombre: string;
    direccion?: string;
    estado: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

@Injectable({ providedIn: 'root' })
export class UbicacionService {
    private apiUrl: string;

    constructor(private http: HttpClient) {
        this.apiUrl = environment.apiUrl;
    }

    getAreas(page = 1, perPage = 10): Observable<PaginatedResponse<Area>> {
        return this.http.get<PaginatedResponse<Area>>(`${this.apiUrl}/areas`, {
            params: { page: page.toString(), per_page: perPage.toString() }
        });
    }

    getAllAreas(): Observable<Area[]> {
        return this.http.get<Area[]>(`${this.apiUrl}/areas/all`);
    }

    createArea(data: Partial<Area>): Observable<any> {
        return this.http.post(`${this.apiUrl}/areas`, data);
    }

    updateArea(id: number, data: Partial<Area>): Observable<any> {
        return this.http.put(`${this.apiUrl}/areas/${id}`, data);
    }

    deleteArea(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/areas/${id}`);
    }

    getUbicaciones(page = 1, perPage = 10): Observable<PaginatedResponse<Ubicacion>> {
        return this.http.get<PaginatedResponse<Ubicacion>>(`${this.apiUrl}/ubicaciones`, {
            params: { page: page.toString(), per_page: perPage.toString() }
        });
    }

    getAllUbicaciones(): Observable<Ubicacion[]> {
        return this.http.get<Ubicacion[]>(`${this.apiUrl}/ubicaciones/all`);
    }

    createUbicacion(data: Partial<Ubicacion>): Observable<any> {
        return this.http.post(`${this.apiUrl}/ubicaciones`, data);
    }

    updateUbicacion(id: number, data: Partial<Ubicacion>): Observable<any> {
        return this.http.put(`${this.apiUrl}/ubicaciones/${id}`, data);
    }

    deleteUbicacion(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/ubicaciones/${id}`);
    }
}
