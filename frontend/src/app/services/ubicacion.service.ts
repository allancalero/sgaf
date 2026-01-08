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

@Injectable({ providedIn: 'root' })
export class UbicacionService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    getAreas(): Observable<Area[]> {
        return this.http.get<Area[]>(`${this.apiUrl}/areas`);
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

    getUbicaciones(): Observable<Ubicacion[]> {
        return this.http.get<Ubicacion[]>(`${this.apiUrl}/ubicaciones`);
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
