<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Depreciación</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 5px 0; }
        .header p { margin: 3px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #4F46E5; color: white; padding: 6px; text-align: left; font-size: 8px; }
        td { padding: 5px; border-bottom: 1px solid #ddd; font-size: 8px; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #666; }
        .totals { margin: 15px 0; font-weight: bold; }
        tfoot tr { background-color: #f3f4f6; font-weight: bold; border-top: 2px solid #4F46E5; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $system->nombre_alcaldia ?? 'SGAF' }}</h1>
        <p>Reporte de Depreciación de Activos Fijos</p>
        <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="totals">
        <p>Valor Original Total: {{ $system->moneda ?? 'C$' }}{{ number_format($totales['valor_original'], 2) }}</p>
        <p>Depreciación Acumulada: {{ $system->moneda ?? 'C$' }}{{ number_format($totales['depreciacion_acumulada'], 2) }}</p>
        <p>Valor en Libros Total: {{ $system->moneda ?? 'C$' }}{{ number_format($totales['valor_libros'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Activo</th>
                <th>Fecha Adq.</th>
                <th>Precio Original</th>
                <th>Vida Útil</th>
                <th>Valor Residual</th>
                <th>Depr. Anual</th>
                <th>Depr. Acumulada</th>
                <th>Valor Libros</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activos as $activo)
            <tr>
                <td>{{ $activo->codigo_inventario }}</td>
                <td>{{ $activo->nombre_activo }}</td>
                <td>{{ $activo->fecha_adquisicion }}</td>
                <td>{{ $system->moneda ?? 'C$' }}{{ number_format($activo->precio_adquisicion, 2) }}</td>
                <td>{{ $activo->vida_util_anos }} años</td>
                <td>{{ $system->moneda ?? 'C$' }}{{ number_format($activo->valor_residual, 2) }}</td>
                <td>{{ $system->moneda ?? 'C$' }}{{ number_format($activo->depreciacion_anual, 2) }}</td>
                <td>{{ $system->moneda ?? 'C$' }}{{ number_format($activo->depreciacion_acumulada, 2) }}</td>
                <td>{{ $system->moneda ?? 'C$' }}{{ number_format($activo->valor_libros, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align: right; padding: 8px;">TOTALES:</td>
                <td style="padding: 8px;">{{ $system->moneda ?? 'C$' }}{{ number_format($totales['depreciacion_acumulada'], 2) }}</td>
                <td style="padding: 8px;">{{ $system->moneda ?? 'C$' }}{{ number_format($totales['valor_libros'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>_____________________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; _____________________</p>
        <p>{{ $system->alcaldesa ?? 'Alcaldesa' }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $system->gerente ?? 'Gerente' }}</p>
    </div>
</body>
</html>
