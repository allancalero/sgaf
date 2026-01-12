import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Personal {
    id: number;
    nombre: string;
    apellido: string;
    cargo_id?: number;
    cargo?: string;
    area_id?: number;
    area?: string;
    telefono?: string;
    email?: string;
    numero_cedula?: string;
    numero_empleado?: string; // New field
    sexo?: string;
    direccion?: string;
    estado: string;
    foto?: string; // New field
}

export interface Cargo {
    id: number;
    nombre: string;
    estado: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
}

@Injectable({ providedIn: 'root' })
export class PersonalService {
    private apiUrl: string;

    constructor(private http: HttpClient) {
        this.apiUrl = environment.apiUrl;
    }

    getPersonal(page = 1, search = '', areaId = '', perPage = 10): Observable<PaginatedResponse<Personal>> {
        let params = new HttpParams()
            .set('page', page.toString())
            .set('per_page', perPage.toString());
        if (search) params = params.set('search', search);
        if (areaId) params = params.set('area_id', areaId);
        return this.http.get<PaginatedResponse<Personal>>(`${this.apiUrl}/personal`, { params });
    }

    getAllPersonal(areaId: any = ''): Observable<Personal[]> {
        let params = new HttpParams();
        if (areaId) {
            params = params.set('area_id', areaId.toString());
        }
        return this.http.get<Personal[]>(`${this.apiUrl}/personal/all`, { params });
    }

    createPersonal(data: Partial<Personal> | FormData): Observable<any> {
        return this.http.post(`${this.apiUrl}/personal`, data);
    }

    updatePersonal(id: number, data: Partial<Personal> | FormData): Observable<any> {
        if (data instanceof FormData) {
            data.append('_method', 'PUT');
            return this.http.post(`${this.apiUrl}/personal/${id}`, data);
        }
        return this.http.put(`${this.apiUrl}/personal/${id}`, data);
    }

    deletePersonal(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/personal/${id}`);
    }

    getCargos(page = 1, perPage = 10): Observable<PaginatedResponse<Cargo>> {
        let params = new HttpParams()
            .set('page', page.toString())
            .set('per_page', perPage.toString());
        return this.http.get<PaginatedResponse<Cargo>>(`${this.apiUrl}/cargos`, { params });
    }

    getAllCargos(): Observable<Cargo[]> {
        return this.http.get<Cargo[]>(`${this.apiUrl}/cargos/all`);
    }

    createCargo(data: Partial<Cargo>): Observable<any> {
        return this.http.post(`${this.apiUrl}/cargos`, data);
    }

    updateCargo(id: number, data: Partial<Cargo>): Observable<any> {
        return this.http.put(`${this.apiUrl}/cargos/${id}`, data);
    }

    deleteCargo(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/cargos/${id}`);
    }
}
