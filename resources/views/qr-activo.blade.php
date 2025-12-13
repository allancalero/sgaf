<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Activo {{ $detalle->codigo_inventario }}</title>
    <style>
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; background: #f4f5f7; margin: 0; padding: 24px; color: #1f2937; }
        .card { max-width: 520px; margin: 0 auto; background: white; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .qr { display: flex; justify-content: center; padding: 12px; background: #f8fafc; border-radius: 12px; }
        dl { display: grid; grid-template-columns: 1fr 2fr; gap: 8px 12px; margin: 16px 0 0; font-size: 14px; }
        dt { color: #6b7280; font-weight: 600; }
        dd { margin: 0; color: #111827; }
        .small { color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="qr">{!! $svg !!}</div>
        <dl>
            <dt>Código</dt><dd>{{ $detalle->codigo_inventario }}</dd>
            <dt>Activo</dt><dd>{{ $detalle->nombre_activo }}</dd>
            <dt>Estado</dt><dd>{{ $detalle->estado }}</dd>
            <dt>Área</dt><dd>{{ $detalle->area_nombre }}</dd>
            <dt>Ubicación</dt><dd>{{ $detalle->ubicacion_nombre }}</dd>
            <dt>Responsable</dt><dd>{{ $detalle->responsable_nombre ?? '—' }}</dd>
            <dt>Adquisición</dt><dd>{{ $detalle->fecha_adquisicion ?? '—' }}</dd>
        </dl>
        <p class="small">Escanea para ver detalle en inventario.</p>
    </div>
</body>
</html>
