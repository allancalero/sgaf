<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Trazabilidad</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 5px 0; }
        .header p { margin: 3px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #4F46E5; color: white; padding: 6px; text-align: left; font-size: 9px; }
        td { padding: 5px; border-bottom: 1px solid #ddd; font-size: 9px; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $system->nombre_alcaldia ?? 'SGAF' }}</h1>
        <p>Reporte de Trazabilidad de Activos</p>
        <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Código</th>
                <th>Activo</th>
                <th>Desde</th>
                <th>Hacia</th>
                <th>Área Desde</th>
                <th>Área Hacia</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $mov)
            <tr>
                <td>{{ $mov->fecha_asignacion }}</td>
                <td>{{ $mov->codigo_inventario }}</td>
                <td>{{ $mov->nombre_activo }}</td>
                <td>{{ $mov->desde ?? 'N/A' }}</td>
                <td>{{ $mov->hacia }}</td>
                <td>{{ $mov->area_desde ?? 'N/A' }}</td>
                <td>{{ $mov->area_hacia ?? 'N/A' }}</td>
                <td>{{ $mov->motivo ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>_____________________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; _____________________</p>
        <p>{{ $system->alcaldesa ?? 'Alcaldesa' }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $system->gerente ?? 'Gerente' }}</p>
    </div>
</body>
</html>
