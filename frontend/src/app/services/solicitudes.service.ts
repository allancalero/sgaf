import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Solicitud {
    id: number;
    activo_id: number;
    solicitante_id: number;
    motivo: string;
    estado: 'PENDIENTE' | 'APROBADO' | 'RECHAZADO';
    procesado_por?: number;
    nota_admin?: string;
    created_at: string;
    updated_at: string;
    activo?: any;
    solicitante?: any;
    procesador?: any;
}

@Injectable({
    providedIn: 'root'
})
export class SolicitudesService {
    private apiUrl = `${environment.apiUrl}/solicitudes`;

    constructor(private http: HttpClient) { }

    getSolicitudes(estado?: string): Observable<any> {
        let params = new HttpParams();
        if (estado) {
            params = params.set('estado', estado);
        }
        return this.http.get<any>(this.apiUrl, { params });
    }

    createSolicitud(activoId: number, motivo: string): Observable<any> {
        return this.http.post(this.apiUrl, { activo_id: activoId, motivo });
    }

    approveSolicitud(id: number, notaAdmin?: string): Observable<any> {
        return this.http.patch(`${this.apiUrl}/${id}/aprobar`, { nota_admin: notaAdmin });
    }

    rejectSolicitud(id: number, notaAdmin: string): Observable<any> {
        return this.http.patch(`${this.apiUrl}/${id}/rechazar`, { nota_admin: notaAdmin });
    }

    // Helper for admin checks
    canManage(user: any): boolean {
        return user?.roles?.some((r: any) => r.slug === 'admin' || r.slug === 'super-admin');
    }
}
