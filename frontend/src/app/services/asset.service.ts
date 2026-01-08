import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Asset, AssetResponse } from '../models/asset.model';

@Injectable({
    providedIn: 'root'
})
export class AssetService {
    private apiUrl = `${environment.apiUrl}/assets`;

    constructor(private http: HttpClient) { }

    getAssets(page: number = 1, search: string = '', estado: string = ''): Observable<AssetResponse> {
        let params = new HttpParams()
            .set('page', page.toString());

        if (search) {
            params = params.set('search', search);
        }

        if (estado) {
            params = params.set('estado', estado);
        }

        return this.http.get<AssetResponse>(this.apiUrl, { params });
    }

    getAsset(id: number): Observable<Asset> {
        return this.http.get<Asset>(`${this.apiUrl}/${id}`);
    }

    createAsset(data: any): Observable<Asset> {
        return this.http.post<Asset>(this.apiUrl, data);
    }

    updateAsset(id: number, data: any): Observable<Asset> {
        return this.http.put<Asset>(`${this.apiUrl}/${id}`, data);
    }

    deleteAsset(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/${id}`);
    }

    getClasificaciones(): Observable<any[]> {
        return this.http.get<any[]>(`${this.apiUrl}/clasificaciones`);
    }

    getFuentes(): Observable<any[]> {
        return this.http.get<any[]>(`${this.apiUrl}/fuentes`);
    }

    getTipos(): Observable<any[]> {
        return this.http.get<any[]>(`${this.apiUrl}/tipos`);
    }

    getProveedores(): Observable<any[]> {
        return this.http.get<any[]>(`${this.apiUrl}/proveedores`);
    }

    getCheques(): Observable<any[]> {
        return this.http.get<any[]>(`${this.apiUrl}/cheques`);
    }
}
