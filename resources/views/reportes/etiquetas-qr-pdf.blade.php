<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Etiquetas QR - {{ $system->nombre_alcaldia ?? 'SGAF' }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 0.25in 0.35in;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
        }
        
        .etiquetas-grid {
            width: 100%;
        }
        
        .etiqueta {
            width: 3.8in;
            height: 3.3in;
            border: 1px dashed #999;
            display: inline-block;
            vertical-align: top;
            text-align: center;
            padding: 0.15in;
            margin-bottom: 0.15in;
            page-break-inside: avoid;
            position: relative;
        }
        
        .etiqueta:nth-child(odd) {
            margin-right: 0.25in;
        }
        
        /* Forzar salto de página cada 6 etiquetas */
        .etiqueta:nth-child(6n) {
            page-break-after: always;
        }
        
        .logo-container {
            height: 0.4in;
            margin-bottom: 0.1in;
        }
        
        .logo {
            max-height: 0.4in;
            max-width: 1.2in;
        }
        
        .qr-container {
            margin: 0.15in auto 0.15in;
            text-align: center;
        }
        
        .qr-code {
            width: 1.6in;
            height: 1.6in;
            display: inline-block;
        }
        
        .codigo {
            font-size: 11pt;
            font-weight: bold;
            margin: 0.1in 0;
            color: #000;
            font-family: 'Courier New', monospace;
        }
        
        .nombre {
            font-size: 8.5pt;
            line-height: 1.2;
            color: #333;
            margin-top: 0.05in;
            max-height: 0.6in;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .area {
            font-size: 7pt;
            color: #666;
            margin-top: 0.05in;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="etiquetas-grid">
        @foreach($activos as $activo)
        <div class="etiqueta">
            @if($logoBase64)
            <div class="logo-container">
                <img src="{{ $logoBase64 }}" alt="Logo" class="logo">
            </div>
            @endif
            
            <div class="qr-container">
                <img src="{{ $activo['qr'] }}" alt="QR" class="qr-code">
            </div>
            
            <div class="codigo">{{ $activo['codigo'] }}</div>
            
            <div class="nombre">{{ Str::limit($activo['nombre'], 60) }}</div>
            
            @if($activo['area'])
            <div class="area">{{ $activo['area'] }}</div>
            @endif
        </div>
        @endforeach
    </div>
</body>
</html>
