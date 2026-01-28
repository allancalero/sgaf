import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormBuilder, FormGroup, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';
import Swal from 'sweetalert2';
import { PasswordInputComponent } from '../../components/password-input/password-input.component';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faLock, faExclamationCircle } from '@fortawesome/free-solid-svg-icons';

@Component({
    selector: 'app-login',
    standalone: true,
    imports: [CommonModule, FormsModule, ReactiveFormsModule, PasswordInputComponent, FontAwesomeModule],
    templateUrl: './login.component.html',
    styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
    credentials = {
        email: '',
        password: ''
    };
    isLoading = false;

    // Icons
    faLock = faLock;
    faExclamationCircle = faExclamationCircle;

    // Password Change State
    showChangePasswordModal = false;
    changePasswordForm: FormGroup;
    isChangingPassword = false;

    constructor(
        private authService: AuthService,
        private router: Router,
        private fb: FormBuilder
    ) {
        this.changePasswordForm = this.fb.group({
            currentPassword: ['', Validators.required],
            newPassword: ['', [
                Validators.required,
                Validators.minLength(8),
                this.passwordComplexityValidator
            ]],
            confirmPassword: ['', Validators.required]
        }, { validators: this.passwordMatchValidator });
    }

    ngOnInit() {
        // If user is already logged in but caught by guard due to password change
        if (this.authService.isLoggedIn()) {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            if (user.must_change_password) {
                this.showChangePasswordModal = true;
            } else {
                this.router.navigate(['/dashboard']);
            }
        }
    }

    onSubmit() {
        this.isLoading = true;
        this.authService.login(this.credentials).subscribe({
            next: (res) => {
                this.isLoading = false;
                if (res.user.must_change_password) {
                    this.showChangePasswordModal = true;
                    // Pre-fill current password if we want to be nice, 
                    // but security wise better to ask them to type it or just type new one.
                    // The backend changePassword expects 'current_password'. 
                    // We can autopopulate it from credentials if we trust it's fresh.
                    this.changePasswordForm.patchValue({
                        currentPassword: this.credentials.password
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        text: 'Inicio de sesión exitoso',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    this.router.navigate(['/dashboard']);
                }
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

    onChangePasswordSubmit() {
        if (this.changePasswordForm.invalid) return;

        this.isChangingPassword = true;
        const { currentPassword, newPassword, confirmPassword } = this.changePasswordForm.value;

        this.authService.changePassword(currentPassword, newPassword, confirmPassword).subscribe({
            next: () => {
                this.isChangingPassword = false;
                this.showChangePasswordModal = false;
                Swal.fire({
                    icon: 'success',
                    title: 'Contraseña Actualizada',
                    text: 'Tu contraseña se ha restablecido correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    this.router.navigate(['/dashboard']);
                });
            },
            error: (err) => {
                this.isChangingPassword = false;
                const msg = err.error?.message || 'Error al actualizar contraseña';
                Swal.fire('Error', msg, 'error');
            }
        });
    }

    // Custom Validator for complexity
    passwordComplexityValidator(control: AbstractControl): ValidationErrors | null {
        const value = control.value;
        if (!value) return null;

        const hasUpperCase = /[A-Z]/.test(value);
        const hasNumber = /[0-9]/.test(value);
        const hasSpecialChar = /[@$!%*#?&]/.test(value);

        const valid = hasUpperCase && hasNumber && hasSpecialChar;

        return valid ? null : { complexity: true };
    }

    // Cross-field validator
    passwordMatchValidator(group: AbstractControl): ValidationErrors | null {
        const pass = group.get('newPassword')?.value;
        const confirm = group.get('confirmPassword')?.value;
        return pass === confirm ? null : { mismatch: true };
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
