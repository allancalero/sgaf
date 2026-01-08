import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { UsuarioService, Usuario } from '../../services/usuario.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-usuarios',
    standalone: true,
    imports: [CommonModule, FormsModule, MainLayoutComponent],
    templateUrl: './usuarios.component.html'
})
export class UsuariosComponent implements OnInit {
    usuarios: Usuario[] = [];
    loading = true;
    search = '';
    showForm = false;

    userForm: any = {
        nombre: '',
        apellido: '',
        email: '',
        password: '',
        rol: 'OPERADOR',
        estado: 'ACTIVO'
    };
    editingId: number | null = null;

    constructor(private usuarioService: UsuarioService) { }

    ngOnInit() { this.loadData(); }

    loadData() {
        this.loading = true;
        this.usuarioService.getUsuarios(1, this.search).subscribe({
            next: (res) => {
                this.usuarios = res.data;
                this.loading = false;
            },
            error: () => this.loading = false
        });
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
}
