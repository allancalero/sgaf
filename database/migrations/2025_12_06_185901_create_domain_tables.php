<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255)->unique();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
        });

        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255)->unique();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
        });

        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255)->unique();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
        });

        Schema::create('clasificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255)->unique();
            $table->timestamps();
        });

        Schema::create('fuentes_financiamiento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
        });

        Schema::create('tipos_activos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255)->unique();
            $table->foreignId('clasificacion_id')->constrained('clasificaciones');
            $table->timestamps();
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('ruc', 100)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->foreignId('cargo_id')->constrained('cargos');
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('ubicacion_id')->constrained('ubicaciones');
            $table->string('telefono', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('numero_empleado', 50)->nullable();
            $table->string('numero_cedula', 50)->nullable();
            $table->date('fecha_nac')->nullable();
            $table->integer('edad');
            $table->string('direccion', 255)->nullable();
            $table->string('profesion', 100)->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->string('foto', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('responsables', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->foreignId('id_cargo')->constrained('cargos');
            $table->foreignId('area_id')->constrained('areas');
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
        });

        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->string('numero_cheque', 50)->unique();
            $table->string('banco', 100);
            $table->string('cuenta_bancaria', 50);
            $table->decimal('monto_total', 12, 2);
            $table->decimal('saldo_disponible', 12, 2)->default(0);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('beneficiario', 255);
            $table->string('beneficiario_ruc', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('EMITIDO');
            $table->foreignId('area_solicitante_id')->constrained('areas');
            $table->foreignId('usuario_emisor_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('activos_fijos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_inventario', 50)->unique();
            $table->string('nombre_activo', 255);
            $table->string('marca', 255)->nullable();
            $table->string('modelo', 255)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('serie', 255)->nullable();
            $table->string('foto', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 12, 2)->nullable();
            $table->decimal('precio_adquisicion', 12, 2)->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->string('numero_factura', 100)->nullable();
            $table->foreignId('cheque_id')->nullable()->constrained('cheques')->nullOnDelete();
            $table->decimal('monto_cheque_utilizado', 12, 2)->nullable();
            $table->string('estado', 20)->default('BUENO');
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('ubicacion_id')->constrained('ubicaciones');
            $table->foreignId('clasificacion_id')->constrained('clasificaciones');
            $table->foreignId('tipo_activo_id')->nullable()->constrained('tipos_activos')->nullOnDelete();
            $table->foreignId('fuente_financiamiento_id')->constrained('fuentes_financiamiento');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->foreignId('personal_id')->nullable()->constrained('personal')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('historial_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos_fijos')->cascadeOnDelete();
            $table->foreignId('asignacion_anterior_id')->nullable()->constrained('personal')->nullOnDelete();
            $table->foreignId('asignacion_nuevo_id')->constrained('personal');
            $table->date('fecha_asignacion');
            $table->text('motivo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_asignaciones');
        Schema::dropIfExists('activos_fijos');
        Schema::dropIfExists('cheques');
        Schema::dropIfExists('responsables');
        Schema::dropIfExists('personal');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('tipos_activos');
        Schema::dropIfExists('fuentes_financiamiento');
        Schema::dropIfExists('clasificaciones');
        Schema::dropIfExists('ubicaciones');
        Schema::dropIfExists('cargos');
        Schema::dropIfExists('areas');
    }
};
