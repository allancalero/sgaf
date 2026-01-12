import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Asset, AssetResponse } from '../models/asset.model';

@Injectable({
    providedIn: 'root'
})
export class AssetService {
    private apiUrl: string;

    constructor(private http: HttpClient) {
        this.apiUrl = `${environment.apiUrl}/assets`;
    }

    getAssets(page: number = 1, search: string = '', estado: string = '', perPage: number = 15, areaId: any = '', personalId: any = '', clasificacionId: any = ''): Observable<AssetResponse> {
        let params = new HttpParams()
            .set('page', page.toString())
            .set('per_page', perPage.toString());

        if (search && search.trim()) {
            params = params.set('search', search.trim());
        }

        if (estado) {
            params = params.set('estado', estado);
        }

        if (areaId && areaId !== '') {
            params = params.set('area_id', areaId.toString());
        }

        if (personalId && personalId !== '') {
            params = params.set('personal_id', personalId.toString());
        }

        if (clasificacionId && clasificacionId !== '') {
            params = params.set('clasificacion_id', clasificacionId.toString());
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
