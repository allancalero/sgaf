import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Proveedor {
    id: number;
    nombre: string;
    ruc?: string;
    direccion?: string;
    telefono?: string;
    email?: string;
}

export interface Clasificacion {
    id: number;
    nombre: string;
}

export interface Fuente {
    id: number;
    nombre: string;
    estado: string;
}

export interface Tipo {
    id: number;
    nombre: string;
    clasificacion_id: number | null;
    clasificacion_nombre?: string;
}

export interface Cheque {
    id: number;
    numero_cheque: string;
    banco: string;
    cuenta_bancaria: string;
    monto_total: number;
    saldo_disponible: number;
    fecha_emision: string;
    fecha_vencimiento?: string;
    beneficiario: string;
    beneficiario_ruc?: string;
    descripcion?: string;
    estado: string;
    area_solicitante_id: number | null;
    usuario_emisor_id: number | null;
    area_nombre?: string;
    usuario_nombre?: string;
}

@Injectable({ providedIn: 'root' })
export class CatalogoActivosService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    // ==================== PROVEEDORES ====================
    getProveedores(): Observable<Proveedor[]> {
        return this.http.get<Proveedor[]>(`${this.apiUrl}/proveedores`);
    }

    createProveedor(data: Partial<Proveedor>): Observable<any> {
        return this.http.post(`${this.apiUrl}/proveedores`, data);
    }

    updateProveedor(id: number, data: Partial<Proveedor>): Observable<any> {
        return this.http.put(`${this.apiUrl}/proveedores/${id}`, data);
    }

    deleteProveedor(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/proveedores/${id}`);
    }

    // ==================== CLASIFICACIONES ====================
    getClasificaciones(): Observable<Clasificacion[]> {
        return this.http.get<Clasificacion[]>(`${this.apiUrl}/clasificaciones`);
    }

    createClasificacion(data: Partial<Clasificacion>): Observable<any> {
        return this.http.post(`${this.apiUrl}/clasificaciones`, data);
    }

    updateClasificacion(id: number, data: Partial<Clasificacion>): Observable<any> {
        return this.http.put(`${this.apiUrl}/clasificaciones/${id}`, data);
    }

    deleteClasificacion(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/clasificaciones/${id}`);
    }

    // ==================== FUENTES ====================
    getFuentes(): Observable<Fuente[]> {
        return this.http.get<Fuente[]>(`${this.apiUrl}/fuentes`);
    }

    createFuente(data: Partial<Fuente>): Observable<any> {
        return this.http.post(`${this.apiUrl}/fuentes`, data);
    }

    updateFuente(id: number, data: Partial<Fuente>): Observable<any> {
        return this.http.put(`${this.apiUrl}/fuentes/${id}`, data);
    }

    deleteFuente(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/fuentes/${id}`);
    }

    // ==================== TIPOS ====================
    getTipos(): Observable<Tipo[]> {
        return this.http.get<Tipo[]>(`${this.apiUrl}/tipos`);
    }

    createTipo(data: Partial<Tipo>): Observable<any> {
        return this.http.post(`${this.apiUrl}/tipos`, data);
    }

    updateTipo(id: number, data: Partial<Tipo>): Observable<any> {
        return this.http.put(`${this.apiUrl}/tipos/${id}`, data);
    }

    deleteTipo(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/tipos/${id}`);
    }

    // ==================== CHEQUES ====================
    getCheques(): Observable<Cheque[]> {
        return this.http.get<Cheque[]>(`${this.apiUrl}/cheques`);
    }

    createCheque(data: Partial<Cheque>): Observable<any> {
        return this.http.post(`${this.apiUrl}/cheques`, data);
    }

    updateCheque(id: number, data: Partial<Cheque>): Observable<any> {
        return this.http.put(`${this.apiUrl}/cheques/${id}`, data);
    }

    deleteCheque(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/cheques/${id}`);
    }
}
