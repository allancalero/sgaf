import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Usuario {
    id: number;
    nombre: string;
    apellido: string;
    email: string;
    rol: string;
    estado: string;
    created_at: string;
}

@Injectable({ providedIn: 'root' })
export class UsuarioService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    getUsuarios(page = 1, search = ''): Observable<any> {
        let params = new HttpParams().set('page', page);
        if (search) params = params.set('search', search);
        return this.http.get(`${this.apiUrl}/usuarios`, { params });
    }

    create(data: Partial<Usuario> & { password: string }): Observable<any> {
        return this.http.post(`${this.apiUrl}/usuarios`, data);
    }

    update(id: number, data: Partial<Usuario>): Observable<any> {
        return this.http.put(`${this.apiUrl}/usuarios/${id}`, data);
    }

    delete(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/usuarios/${id}`);
    }
}
