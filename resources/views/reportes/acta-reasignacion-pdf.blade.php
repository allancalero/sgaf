<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acta de Reasignación - {{ $reasignacion->activo->codigo_inventario ?? 'N/A' }}</title>
    <style>
        @page { margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.5; }
        
        /* Header con Logo, Título y QR */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .header-table td { vertical-align: middle; }
        .logo-cell { width: 20%; text-align: left; }
        .logo-cell img { width: 80px; height: auto; }
        .title-cell { width: 60%; text-align: center; }
        .title-cell h1 { font-size: 16px; margin: 3px 0; color: #1f2937; }
        .title-cell h2 { font-size: 14px; margin: 3px 0; color: #7C3AED; }
        .title-cell p { margin: 2px 0; color: #666; font-size: 10px; }
        .qr-cell { width: 20%; text-align: right; }
        .qr-box { display: inline-block; border: 1px solid #ddd; padding: 8px; border-radius: 4px; background: #f9fafb; }
        .qr-box img { width: 90px; height: 90px; }
        .qr-box p { font-size: 9px; color: #666; margin-top: 4px; text-align: center; }
        
        .header-divider { border-bottom: 3px solid #7C3AED; margin-bottom: 15px; }
        
        /* Secciones de contenido */
        .section { margin-bottom: 12px; }
        .section-title { background: linear-gradient(135deg, #7C3AED, #9333EA); color: white; padding: 6px 12px; font-size: 11px; font-weight: bold; margin-bottom: 8px; border-radius: 3px; }
        
        /* Tablas de datos */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .data-table td { padding: 6px 10px; border: 1px solid #e5e7eb; font-size: 10px; }
        .data-table td:first-child { background: #f3f4f6; font-weight: bold; width: 30%; color: #374151; }
        .highlight { background: #dbeafe; font-weight: bold; color: #1e40af; }
        .highlight-purple { background: #ede9fe; font-weight: bold; color: #6b21a8; }
        
        /* Layout de dos columnas para datos */
        .two-columns { display: table; width: 100%; margin-bottom: 12px; }
        .column { display: table-cell; width: 50%; vertical-align: top; }
        .column:first-child { padding-right: 8px; }
        .column:last-child { padding-left: 8px; }
        
        /* Transferencia visual */
        .transfer-section { margin: 15px 0; }
        .transfer-box { display: table; width: 100%; }
        .transfer-from, .transfer-to { display: table-cell; width: 45%; vertical-align: top; }
        .transfer-arrow { display: table-cell; width: 10%; text-align: center; vertical-align: middle; font-size: 24px; color: #7C3AED; }
        .transfer-label { font-weight: bold; font-size: 10px; color: #666; margin-bottom: 4px; text-align: center; }
        .transfer-content { border: 2px solid #e5e7eb; border-radius: 6px; padding: 10px; background: #f9fafb; }
        .transfer-content.from { border-color: #fbbf24; background: #fffbeb; }
        .transfer-content.to { border-color: #22c55e; background: #f0fdf4; }
        .transfer-name { font-weight: bold; font-size: 11px; color: #1f2937; }
        .transfer-detail { font-size: 9px; color: #666; margin-top: 2px; }
        
        /* Motivo */
        .motivo-box { background: #fef3c7; border: 1px solid #fbbf24; padding: 10px 15px; margin: 10px 0; border-radius: 5px; }
        .motivo-box h3 { font-size: 11px; color: #92400e; margin-bottom: 5px; }
        .motivo-box p { font-size: 10px; color: #78350f; }
        
        /* Términos y condiciones */
        .terms { background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border: 1px solid #d1d5db; padding: 10px 15px; margin: 15px 0; border-radius: 5px; }
        .terms h3 { font-size: 11px; color: #374151; margin-bottom: 8px; font-weight: bold; }
        .terms ol { margin: 0; padding-left: 18px; }
        .terms li { margin: 4px 0; font-size: 10px; line-height: 1.4; }
        
        /* Firmas */
        .signatures { margin-top: 25px; display: table; width: 100%; }
        .sig-box { display: table-cell; text-align: center; width: 33.33%; padding: 0 10px; }
        .sig-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 5px; }
        .sig-name { font-weight: bold; font-size: 10px; color: #1f2937; }
        .sig-title { font-size: 9px; color: #666; margin-top: 2px; }
        
        /* Footer */
        .footer { margin-top: 15px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    <!-- Encabezado con Logo, Título y QR -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo">
                @endif
            </td>
            <td class="title-cell">
                <h1>{{ $system->nombre_alcaldia ?? 'ALCALDÍA MUNICIPAL' }}</h1>
                <h2>ACTA DE REASIGNACIÓN DE ACTIVO FIJO</h2>
                <p>Fecha de emisión: {{ $fecha_emision }} | No. Reasignación: {{ $reasignacion->id }}</p>
            </td>
            <td class="qr-cell">
                <div class="qr-box">
                    <img src="{{ $qrCode }}" alt="QR Code">
                    <p>{{ $reasignacion->activo->codigo_inventario ?? 'N/A' }}</p>
                </div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>

    <!-- Datos del Activo -->
    <div class="section">
        <div class="section-title">I. DATOS DEL ACTIVO</div>
        <table class="data-table">
            <tr><td>Código Inventario:</td><td class="highlight">{{ $reasignacion->activo->codigo_inventario ?? 'N/A' }}</td></tr>
            <tr><td>Nombre del Activo:</td><td>{{ $reasignacion->activo->nombre_activo ?? 'N/A' }}</td></tr>
            <tr><td>Clasificación:</td><td>{{ $reasignacion->activo->clasificacion->nombre ?? 'N/A' }}</td></tr>
            <tr><td>Marca / Modelo:</td><td>{{ $reasignacion->activo->marca ?? '-' }} / {{ $reasignacion->activo->modelo ?? '-' }}</td></tr>
            <tr><td>Serie:</td><td>{{ $reasignacion->activo->serie ?? '-' }}</td></tr>
            <tr><td>Estado:</td><td><strong>{{ $reasignacion->activo->estado ?? 'N/A' }}</strong></td></tr>
        </table>
    </div>

    <!-- Transferencia Visual -->
    <div class="section">
        <div class="section-title">II. DETALLES DE REASIGNACIÓN</div>
        <div class="transfer-box">
            <div class="transfer-from">
                <p class="transfer-label">ENTREGA (Anterior)</p>
                <div class="transfer-content from">
                    <p class="transfer-name">{{ $reasignacion->responsableAnterior ? $reasignacion->responsableAnterior->nombre . ' ' . $reasignacion->responsableAnterior->apellido : 'Sin asignar' }}</p>
                    <p class="transfer-detail">Ubicación: {{ $reasignacion->ubicacionAnterior->nombre ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="transfer-arrow">→</div>
            <div class="transfer-to">
                <p class="transfer-label">RECIBE (Nuevo)</p>
                <div class="transfer-content to">
                    <p class="transfer-name">{{ $reasignacion->responsableNuevo ? $reasignacion->responsableNuevo->nombre . ' ' . $reasignacion->responsableNuevo->apellido : 'Sin asignar' }}</p>
                    <p class="transfer-detail">Ubicación: {{ $reasignacion->ubicacionNueva->nombre ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        <table class="data-table" style="margin-top: 10px;">
            <tr><td>Fecha de Reasignación:</td><td class="highlight-purple">{{ $reasignacion->fecha_reasignacion->format('d/m/Y') }}</td></tr>
            <tr><td>Registrado por:</td><td>{{ $reasignacion->usuario->name ?? 'Sistema' }}</td></tr>
        </table>
    </div>

    <!-- Motivo -->
    <div class="motivo-box">
        <h3>III. MOTIVO DE LA REASIGNACIÓN</h3>
        <p>{{ $reasignacion->motivo }}</p>
        @if($reasignacion->observaciones)
        <p style="margin-top: 8px; font-style: italic;"><strong>Observaciones:</strong> {{ $reasignacion->observaciones }}</p>
        @endif
    </div>

    <!-- Términos y Condiciones -->
    <div class="terms">
        <h3>IV. TÉRMINOS Y CONDICIONES</h3>
        <ol>
            <li>El nuevo responsable acepta el activo en el estado descrito, comprometiéndose a su buen uso y cuidado.</li>
            <li>El responsable anterior queda liberado de la custodia del activo a partir de la fecha de reasignación.</li>
            <li>Cualquier daño, pérdida o deterioro posterior deberá ser reportado por el nuevo responsable.</li>
            <li>El activo debe permanecer en la ubicación asignada salvo autorización expresa.</li>
        </ol>
    </div>

    <!-- Firmas -->
    <div class="signatures">
        <div class="sig-box">
            <div class="sig-line">
                <p class="sig-name">{{ $reasignacion->responsableAnterior ? $reasignacion->responsableAnterior->nombre . ' ' . $reasignacion->responsableAnterior->apellido : '________________' }}</p>
                <p class="sig-title">Responsable Anterior (Entrega)</p>
            </div>
        </div>
        <div class="sig-box">
            <div class="sig-line">
                <p class="sig-name">{{ $reasignacion->responsableNuevo ? $reasignacion->responsableNuevo->nombre . ' ' . $reasignacion->responsableNuevo->apellido : '________________' }}</p>
                <p class="sig-title">Responsable Nuevo (Recibe)</p>
            </div>
        </div>
        <div class="sig-box">
            <div class="sig-line">
                <p class="sig-name">{{ $system->gerente ?? 'Encargado de Activos' }}</p>
                <p class="sig-title">Gerente / Encargado Activos Fijos</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Documento válido con firmas autorizadas | {{ $system->nombre_alcaldia ?? 'SGAF' }} {{ date('Y') }} | Sistema de Gestión de Activos Fijos</p>
    </div>
</body>
</html>
