<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Inventario</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 5px 0; }
        .header p { margin: 3px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #4F46E5; color: white; padding: 6px; text-align: left; font-size: 9px; }
        td { padding: 5px; border-bottom: 1px solid #ddd; font-size: 9px; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #666; }
        .footer .cargo { font-weight: bold; color: #4F46E5; font-size: 9px; display: block; margin-top: 2px; }
        .totals { margin: 15px 0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $system->nombre_alcaldia ?? 'SGAF' }}</h1>
        <p>Reporte de Inventario de Activos Fijos</p>
        <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="totals">
        <p>Total de Activos: {{ $totales['cantidad'] }}</p>
        <p>Valor Total: {{ $system->moneda ?? 'C$' }}{{ number_format($totales['valor'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Área</th>
                <th>Clasificación</th>
                <th>Ubicación</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activos as $activo)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $activo->codigo_inventario }}</td>
                <td>{{ $activo->nombre_activo }}</td>
                <td>{{ $activo->area }}</td>
                <td>{{ $activo->clasificacion }}</td>
                <td>{{ $activo->ubicacion }}</td>
                <td>{{ $activo->responsable ?? 'N/A' }}</td>
                <td>{{ $activo->estado }}</td>
                <td>{{ $system->moneda ?? 'C$' }}{{ number_format($activo->precio_adquisicion, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f3f4f6; font-weight: bold; border-top: 2px solid #4F46E5;">
                <td colspan="8" style="text-align: right; padding: 8px;">TOTAL:</td>
                <td style="padding: 8px;">{{ $system->moneda ?? 'C$' }}{{ number_format($totales['valor'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <table style="width: 100%; margin-top: 30px;">
            <tr>
                <td style="width: 25%; text-align: center; vertical-align: top;">
                    <p style="margin-top: 40px; border-top: 1px solid #000; padding-top: 5px; font-weight: bold;">{{ $system->responsable_activo_fijo ?? '____________________' }}</p>
                    <p class="cargo">Responsable de Activo Fijo</p>
                </td>
                <td style="width: 25%; text-align: center; vertical-align: top;">
                    <p style="margin-top: 40px; border-top: 1px solid #000; padding-top: 5px; font-weight: bold;">{{ $system->director_administrativo ?? '____________________' }}</p>
                    <p class="cargo">Director Administrativo</p>
                </td>
                <td style="width: 25%; text-align: center; vertical-align: top;">
                    <p style="margin-top: 40px; border-top: 1px solid #000; padding-top: 5px; font-weight: bold;">{{ $system->gerente ?? '____________________' }}</p>
                    <p class="cargo">Gerente</p>
                </td>
                <td style="width: 25%; text-align: center; vertical-align: top;">
                    <p style="margin-top: 40px; border-top: 1px solid #000; padding-top: 5px; font-weight: bold;">{{ $system->alcaldesa ?? '____________________' }}</p>
                    <p class="cargo">Alcaldesa Municipal</p>
                </td>
            </tr>
        </table>
        <p style="margin-top: 20px; font-size: 8px; color: #999;">Generado el {{ now()->format('d/m/Y H:i:s') }} por {{ $usuario ?? 'Sistema' }}</p>
    </div>
</body>
</html>
