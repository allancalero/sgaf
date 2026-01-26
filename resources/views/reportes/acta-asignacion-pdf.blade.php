<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acta de Asignación - {{ $activo->codigo_inventario }}</title>
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
        .title-cell h2 { font-size: 14px; margin: 3px 0; color: #4F46E5; }
        .title-cell p { margin: 2px 0; color: #666; font-size: 10px; }
        .qr-cell { width: 20%; text-align: right; }
        .qr-box { display: inline-block; border: 1px solid #ddd; padding: 8px; border-radius: 4px; background: #f9fafb; }
        .qr-box img { width: 90px; height: 90px; }
        .qr-box p { font-size: 9px; color: #666; margin-top: 4px; text-align: center; }
        
        .header-divider { border-bottom: 3px solid #4F46E5; margin-bottom: 15px; }
        
        /* Secciones de contenido */
        .section { margin-bottom: 12px; }
        .section-title { background: linear-gradient(135deg, #4F46E5, #7C3AED); color: white; padding: 6px 12px; font-size: 11px; font-weight: bold; margin-bottom: 8px; border-radius: 3px; }
        
        /* Tablas de datos */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .data-table td { padding: 6px 10px; border: 1px solid #e5e7eb; font-size: 10px; }
        .data-table td:first-child { background: #f3f4f6; font-weight: bold; width: 30%; color: #374151; }
        .highlight { background: #dbeafe; font-weight: bold; color: #1e40af; }
        
        /* Layout de dos columnas para datos */
        .two-columns { display: table; width: 100%; margin-bottom: 12px; }
        .column { display: table-cell; width: 50%; vertical-align: top; }
        .column:first-child { padding-right: 8px; }
        .column:last-child { padding-left: 8px; }
        
        /* Términos y condiciones */
        .terms { background: linear-gradient(135deg, #fef3c7, #fde68a); border: 1px solid #fbbf24; padding: 10px 15px; margin: 15px 0; border-radius: 5px; }
        .terms h3 { font-size: 11px; color: #92400e; margin-bottom: 8px; font-weight: bold; }
        .terms ol { margin: 0; padding-left: 18px; }
        .terms li { margin: 4px 0; font-size: 10px; line-height: 1.4; }
        
        /* Firmas */
        .signatures { margin-top: 25px; display: table; width: 100%; }
        .sig-box { display: table-cell; text-align: center; width: 25%; padding: 0 5px; }
        .sig-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 5px; }
        .sig-name { font-weight: bold; font-size: 10px; color: #1f2937; }
        .sig-title { font-size: 10px; color: #4F46E5; margin-top: 3px; font-weight: bold; }
        
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
                <h1>{{ optional($system)->nombre_alcaldia ?? 'ALCALDÍA MUNICIPAL' }}</h1>
                <h2>ACTA DE ASIGNACIÓN DE ACTIVO FIJO</h2>
                <p>Fecha de emisión: {{ $fecha_emision }}</p>
            </td>
            <td class="qr-cell">
                <div class="qr-box">
                    <img src="{{ $qrCode }}" alt="QR Code">
                    <p>{{ $activo->codigo_inventario }}</p>
                </div>
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>

    <!-- Contenido en dos columnas -->
    <div class="two-columns">
        <div class="column">
            <div class="section">
                <div class="section-title">I. DATOS DEL ACTIVO</div>
                <table class="data-table">
                    <tr><td>Código:</td><td class="highlight">{{ $activo->codigo_inventario }}</td></tr>
                    <tr><td>Nombre:</td><td>{{ $activo->nombre_activo }}</td></tr>
                    <tr><td>Clasificación:</td><td>{{ optional($activo->clasificacion)->nombre ?? 'N/A' }}</td></tr>
                    <tr><td>Marca / Modelo:</td><td>{{ $activo->marca ?? '-' }} / {{ $activo->modelo ?? '-' }}</td></tr>
                    <tr><td>Serie / Color:</td><td>{{ $activo->serie ?? '-' }} / {{ $activo->color ?? '-' }}</td></tr>
                    <tr><td>Estado:</td><td><strong>{{ $activo->estado }}</strong></td></tr>
                    <tr><td>Valor:</td><td><strong>{{ optional($system)->moneda ?? 'C$' }}{{ number_format($activo->precio_adquisicion, 2) }}</strong></td></tr>
                    <tr><td>Fecha Adquisición:</td><td>{{ $activo->fecha_adquisicion }}</td></tr>
                </table>
            </div>
        </div>
        
        <div class="column">
            <div class="section">
                <div class="section-title">II. DATOS DE ASIGNACIÓN</div>
                <table class="data-table">
                    <tr><td>Responsable:</td><td class="highlight">{{ optional($activo->personal)->nombre ?? '' }} {{ optional($activo->personal)->apellido ?? '' }}</td></tr>
                    <tr><td>Área:</td><td>{{ optional($activo->area)->nombre ?? 'N/A' }}</td></tr>
                    <tr><td>Ubicación:</td><td>{{ optional($activo->ubicacion)->nombre ?? 'N/A' }}</td></tr>
                    <tr><td>Fecha Asignación:</td><td>{{ $fecha_emision }}</td></tr>
                </table>
            </div>
            
            @if($activo->descripcion)
            <div class="section">
                <div class="section-title">OBSERVACIONES</div>
                <table class="data-table">
                    <tr><td colspan="2" style="background: white;">{{ $activo->descripcion }}</td></tr>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="terms">
        <h3>III. TÉRMINOS Y CONDICIONES</h3>
        <ol>
            <li>El responsable se compromete a hacer buen uso del activo, manteniéndolo en condiciones óptimas.</li>
            <li>Cualquier daño, pérdida o deterioro deberá ser reportado inmediatamente al Encargado de Activos.</li>
            <li>No podrá trasladar o prestar el activo a terceros sin autorización previa por escrito.</li>
            <li>El activo deberá devolverse en las mismas condiciones, considerando desgaste normal.</li>
            <li>El incumplimiento implicará responsabilidad administrativa y/o patrimonial.</li>
        </ol>
    </div>

    <div class="signatures">
        <div class="sig-box">
            <div class="sig-line">
                <p class="sig-name">{{ optional($activo->personal)->nombre ?? '' }} {{ optional($activo->personal)->apellido ?? '' }}</p>
                <p class="sig-title">Responsable del Activo</p>
            </div>
        </div>
        <div class="sig-box">
            <div class="sig-line">
                <p class="sig-name">{{ optional($system)->responsable_activo_fijo ?? '____________________' }}</p>
                <p class="sig-title">Responsable de Activo Fijo</p>
            </div>
        </div>
        <div class="sig-box">
            <div class="sig-line">
                <p class="sig-name">{{ optional($system)->director_administrativo ?? '____________________' }}</p>
                <p class="sig-title">Director Administrativo</p>
            </div>
        </div>
        <div class="sig-box">
            <div class="sig-line">
                <p class="sig-name">{{ optional($system)->alcaldesa ?? '____________________' }}</p>
                <p class="sig-title">Alcaldesa Municipal</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }} por {{ $usuario ?? 'Sistema' }} | {{ optional($system)->nombre_alcaldia ?? 'SGAF' }} {{ date('Y') }}</p>
    </div>
</body>
</html>
