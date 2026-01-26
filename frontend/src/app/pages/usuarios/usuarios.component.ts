import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { UsuarioService, Usuario } from '../../services/usuario.service';
import { AuthService } from '../../services/auth.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-usuarios',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './usuarios.component.html'
})
export class UsuariosComponent implements OnInit {
    usuarios: Usuario[] = [];
    loading = true;
    search = '';
    showForm = false;

    currentPage = 1;
    lastPage = 1;
    pageSize = 10;
    totalUsuarios = 0;

    userForm: any = {
        nombre: '',
        apellido: '',
        email: '',
        password: '',
        rol: 'OPERADOR',
        estado: 'ACTIVO'
    };
    editingId: number | null = null;

    constructor(private usuarioService: UsuarioService, private authService: AuthService) { }

    get isAdmin(): boolean {
        // Check local storage or auth service subject
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        return user.role === 'ADMIN' || user.rol === 'ADMIN'; // Handle potential naming inconsistency
    }

    ngOnInit() { this.loadData(); }

    loadData() {
        this.loading = true;
        this.usuarioService.getUsuarios(this.currentPage, this.search).subscribe({
            next: (res: any) => {
                this.usuarios = res.data;
                this.currentPage = res.current_page;
                this.lastPage = res.last_page;
                this.totalUsuarios = res.total;
                this.pageSize = res.per_page;
                this.loading = false;
            },
            error: () => this.loading = false
        });
    }

    changePage(page: number) {
        if (page >= 1 && page <= this.lastPage) {
            this.currentPage = page;
            this.loadData();
        }
    }

    onSearch() { this.loadData(); }

    saveUser() {
        if (!this.userForm.nombre || !this.userForm.email) {
            Swal.fire('Error', 'Nombre y Email son requeridos', 'error');
            return;
        }

        const action = this.editingId
            ? this.usuarioService.update(this.editingId, this.userForm)
            : this.usuarioService.create(this.userForm);

        action.subscribe({
            next: () => {
                Swal.fire('Éxito', this.editingId ? 'Usuario actualizado' : 'Usuario creado', 'success');
                this.closeForm();
                this.loadData();
            },
            error: (err) => Swal.fire('Error', err.error.message || 'Error al procesar', 'error')
        });
    }

    editUser(u: Usuario) {
        this.editingId = u.id;
        this.userForm = { ...u, password: '' }; // Don't show password
        this.showForm = true;
    }

    closeForm() {
        this.showForm = false;
        this.editingId = null;
        this.userForm = {
            nombre: '',
            apellido: '',
            email: '',
            password: '',
            rol: 'OPERADOR',
            estado: 'ACTIVO'
        };
    }

    deleteUsuario(id: number) {
        Swal.fire({
            title: '¿Estás seguro?', text: 'Esta acción no se puede revertir', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                this.usuarioService.delete(id).subscribe(() => {
                    Swal.fire('Eliminado', 'Usuario eliminado', 'success');
                    this.loadData();
                });
            }
        });
    }

    resetPassword(u: Usuario) {
        Swal.fire({
            title: '¿Restablecer contraseña?',
            text: `Se generará una nueva contraseña para ${u.nombre} ${u.apellido}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, restablecer',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                this.usuarioService.resetPassword(u.id).subscribe({
                    next: (res) => {
                        Swal.fire({
                            title: 'Contraseña Restablecida',
                            html: `La nueva contraseña es:<br><strong style="font-size: 1.5rem; color: #3b82f6;">${res.new_password}</strong><br><small style="color: #94a3b8;">Anótela, no se mostrará de nuevo.</small>`,
                            icon: 'success',
                            confirmButtonText: 'Entendido'
                        });
                    },
                    error: (err) => Swal.fire('Error', err.error?.message || 'Error al restablecer', 'error')
                });
            }
        });
    }
}
