import { Routes } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { UbicacionesComponent } from './pages/ubicaciones/ubicaciones.component';
import { RecursosHumanosComponent } from './pages/recursos-humanos/recursos-humanos.component';
import { AssetsComponent } from './pages/assets/assets.component';
import { LoginComponent } from './pages/login/login.component';
import { ReasignacionesComponent } from './pages/reasignaciones/reasignaciones.component';
import { ReportesComponent } from './pages/reportes/reportes.component';
import { DepreciacionComponent } from './pages/depreciacion/depreciacion.component';
import { TrazabilidadComponent } from './pages/trazabilidad/trazabilidad.component';
import { UsuariosComponent } from './pages/usuarios/usuarios.component';
import { AuditoriaComponent } from './pages/auditoria/auditoria.component';
import { RespaldoComponent } from './pages/respaldo/respaldo.component';
import { SeguridadComponent } from './pages/seguridad/seguridad.component';
import { EtiquetasQrComponent } from './pages/etiquetas-qr/etiquetas-qr.component';
import { authGuard } from './guards/auth.guard';

export const routes: Routes = [
    { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
    { path: 'login', component: LoginComponent },
    {
        path: '',
        canActivate: [authGuard],
        children: [
            { path: 'dashboard', component: DashboardComponent },
            { path: 'catalogos/ubicacion', component: UbicacionesComponent },
            { path: 'catalogos/recursos-humanos', component: RecursosHumanosComponent },
            { path: 'activos-fijos', component: AssetsComponent },
            { path: 'activos-fijo-vista', component: AssetsComponent },
            { path: 'activos/mis-activos', component: AssetsComponent },
            { path: 'activos/reasignaciones', component: ReasignacionesComponent },
            { path: 'activos/reportes', component: ReportesComponent },
            { path: 'activos/depreciacion', component: DepreciacionComponent },
            { path: 'activos/trazabilidad', component: TrazabilidadComponent },
            { path: 'activos/etiquetas-qr', component: EtiquetasQrComponent },
            { path: 'sistema/usuarios', component: UsuariosComponent },
            { path: 'sistema/respaldo', component: RespaldoComponent },
            { path: 'sistema/seguridad', component: SeguridadComponent },
            { path: 'sistema/auditoria', component: AuditoriaComponent },
        ]
    }
];
