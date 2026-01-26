<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Inventario</title>
    <style>
        @page {
            margin: 100px 40px 80px 40px;
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px; 
            margin: 0;
            padding: 0;
        }
        
        /* Header fixed at top of each page */
        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 70px;
            text-align: center;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 10px;
        }
        .header-logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 60px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a5f;
            margin: 0;
        }
        .header-subtitle {
            font-size: 11px;
            color: #666;
            margin: 3px 0;
        }
        
        /* Footer fixed at bottom of each page */
        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .footer-left {
            position: absolute;
            left: 0;
            bottom: 0;
        }
        .footer-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 0;
        }
        .footer-right {
            position: absolute;
            right: 0;
            bottom: 0;
        }
        
        /* Content styles */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #4F46E5; color: white; padding: 6px; text-align: left; font-size: 9px; }
        td { padding: 5px; border-bottom: 1px solid #ddd; font-size: 9px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .totals { margin: 10px 0; font-weight: bold; font-size: 11px; }
        .filters-box { background: #f0f9ff; border: 1px solid #0284c7; padding: 8px; margin-bottom: 10px; border-radius: 4px; }
        .filters-box strong { color: #0369a1; }
        .signatures-table { width: 100%; margin-top: 25px; page-break-inside: avoid; }
        .signature-cell { width: 25%; text-align: center; vertical-align: top; }
        .signature-line { margin-top: 35px; border-top: 1px solid #000; padding-top: 5px; font-weight: bold; font-size: 9px; }
        .signature-cargo { font-weight: bold; color: #4F46E5; font-size: 8px; }
    </style>
</head>
<body>
    <!-- Fixed Header -->
    <header>
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="header-logo" alt="Logo">
        @endif
        <p class="header-title">{{ $system->nombre_alcaldia ?? 'ALCALDÍA MUNICIPAL' }}</p>
        <p class="header-subtitle">Reporte de Inventario de Activos Fijos</p>
    </header>

    <!-- Fixed Footer with pagination -->
    <footer>
        <div class="footer-left">Generado por: {{ $usuario ?? 'Sistema' }}</div>
        <div class="footer-right">{{ now()->format('d/m/Y H:i') }}</div>
    </footer>

    <!-- Page content -->
    <main>
        <!-- Totals -->
        <div class="totals">
            <p>Total de Activos: {{ $totales['cantidad'] }} | Valor Total: {{ $system->moneda ?? 'C$' }}{{ number_format($totales['valor'], 2) }}</p>
        </div>

        <!-- Applied Filters -->
        @if(!empty($filters['area_nombre']) || !empty($filters['clasificacion_nombre']) || !empty($filters['ubicacion_nombre']) || !empty($filters['personal_nombre']) || !empty($filters['estado']))
        <div class="filters-box">
            <strong>Filtros aplicados:</strong>
            @if(!empty($filters['area_nombre']))
                <span style="margin-left: 10px;">Área: <strong>{{ $filters['area_nombre'] }}</strong></span>
            @endif
            @if(!empty($filters['clasificacion_nombre']))
                <span style="margin-left: 10px;">Clasificación: <strong>{{ $filters['clasificacion_nombre'] }}</strong></span>
            @endif
            @if(!empty($filters['ubicacion_nombre']))
                <span style="margin-left: 10px;">Ubicación: <strong>{{ $filters['ubicacion_nombre'] }}</strong></span>
            @endif
            @if(!empty($filters['personal_nombre']))
                <span style="margin-left: 10px;">Responsable: <strong>{{ $filters['personal_nombre'] }}</strong></span>
            @endif
            @if(!empty($filters['estado']))
                <span style="margin-left: 10px;">Estado: <strong>{{ $filters['estado'] }}</strong></span>
            @endif
        </div>
        @endif

        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 25px;">#</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    @if(empty($filters['area_nombre']))
                    <th>Área</th>
                    @endif
                    <th>Clasificación</th>
                    @if(empty($filters['personal_nombre']))
                    <th>Responsable</th>
                    @endif
                    <th style="width: 55px;">Estado</th>
                    <th style="width: 80px; text-align: right;">Precio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activos as $activo)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $activo->codigo_inventario }}</td>
                    <td>{{ $activo->nombre_activo }}</td>
                    @if(empty($filters['area_nombre']))
                    <td>{{ $activo->area }}</td>
                    @endif
                    <td>{{ $activo->clasificacion }}</td>
                    @if(empty($filters['personal_nombre']))
                    <td>{{ $activo->responsable ?? 'N/A' }}</td>
                    @endif
                    <td>{{ $activo->estado }}</td>
                    <td style="text-align: right;">{{ $system->moneda ?? 'C$' }}{{ number_format($activo->precio_adquisicion, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $colspan = 4; // # + Código + Nombre + Clasificación
                    if(empty($filters['area_nombre'])) $colspan++;
                    if(empty($filters['personal_nombre'])) $colspan++;
                @endphp
                <tr style="background-color: #f3f4f6; font-weight: bold; border-top: 2px solid #4F46E5;">
                    <td colspan="{{ $colspan }}" style="text-align: right; padding: 8px;">TOTAL:</td>
                    <td style="padding: 8px; text-align: right;">{{ $system->moneda ?? 'C$' }}{{ number_format($totales['valor'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures -->
        <table class="signatures-table">
            <tr>
                <td class="signature-cell">
                    <p class="signature-line">{{ $system->responsable_activo_fijo ?? '____________________' }}</p>
                    <p class="signature-cargo">Responsable de Activo Fijo</p>
                </td>
                <td class="signature-cell">
                    <p class="signature-line">{{ $system->director_administrativo ?? '____________________' }}</p>
                    <p class="signature-cargo">Director Administrativo</p>
                </td>
                <td class="signature-cell">
                    <p class="signature-line">{{ $system->gerente ?? '____________________' }}</p>
                    <p class="signature-cargo">Gerente</p>
                </td>
                <td class="signature-cell">
                    <p class="signature-line">{{ $system->alcaldesa ?? '____________________' }}</p>
                    <p class="signature-cargo">Alcaldesa Municipal</p>
                </td>
            </tr>
        </table>
    </main>

    <!-- Page numbering using DOMPDF inline PHP -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("Arial");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() / 2) - $width;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.4, 0.4, 0.4));
        }
    </script>
</body>
</html>
