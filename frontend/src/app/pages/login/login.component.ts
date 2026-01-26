import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-login',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './login.component.html',
    styleUrls: ['./login.component.css']
})
export class LoginComponent {
    credentials = {
        email: '',
        password: ''
    };
    isLoading = false;

    constructor(private authService: AuthService, private router: Router) {
        console.log('LoginComponent Initialized');
    }

    onSubmit() {
        this.isLoading = true;
        this.authService.login(this.credentials).subscribe({
            next: (res) => {
                this.isLoading = false;
                Swal.fire({
                    icon: 'success',
                    title: '¡Bienvenido!',
                    text: 'Inicio de sesión exitoso',
                    timer: 2000,
                    showConfirmButton: false
                });
                this.router.navigate(['/dashboard']);
            },
            error: (err) => {
                this.isLoading = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.error?.message || err.message || 'Error desconocido',
                    confirmButtonColor: '#4682B4'
                });
            }
        });
    }

    onForgotPassword() {
        Swal.fire({
            title: 'Recuperar Contraseña',
            html: `
                <div class="text-left space-y-4 font-inter">
                    <p class="text-slate-700">Por motivos de seguridad, para restablecer su contraseña debe contactar con el <b>Administrador del Sistema</b>.</p>
                    <div class="bg-slate-100 p-4 rounded-xl space-y-2 border border-slate-200">
                        <p class="text-sm font-bold text-slate-800">Canales de soporte:</p>
                        <p class="text-sm text-slate-600">📍 Departamento de Informática</p>
                        <p class="text-sm text-slate-600">✉️ soporte@tipitapa.gob.ni</p>
                    </div>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#4682B4',
            background: '#ffffff',
            customClass: {
                title: 'font-orbitron text-xl text-slate-800',
                popup: 'rounded-2xl shadow-2xl border border-slate-200'
            }
        });
    }
}
