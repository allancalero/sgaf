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
        <p>_____________________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; _____________________</p>
        <p>{{ $system->alcaldesa ?? 'Alcaldesa' }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $system->gerente ?? 'Gerente' }}</p>
    </div>
</body>
</html>
