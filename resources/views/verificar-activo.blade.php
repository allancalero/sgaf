<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Activo - SGAF</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
            min-height: 100vh;
            color: white;
        }
        
        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header-icon {
            width: 80px;
            height: 80px;
            background: rgba(59, 130, 246, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .header-icon i {
            font-size: 36px;
            color: #60a5fa;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header p {
            color: rgba(147, 197, 253, 0.7);
            font-size: 14px;
            margin-top: 5px;
        }
        
        /* Search Box */
        .search-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            margin-bottom: 20px;
        }
        
        .search-box label {
            font-size: 11px;
            font-weight: 700;
            color: #93c5fd;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 10px;
        }
        
        .search-row {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 14px 18px;
            color: white;
            font-size: 16px;
            font-family: monospace;
        }
        
        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        
        .btn {
            padding: 14px 20px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .btn-scan {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .btn-scan:hover {
            background: rgba(16, 185, 129, 0.3);
        }
        
        .btn-scan.active {
            background: #10b981;
            color: white;
        }
        
        .error-msg {
            color: #f87171;
            font-size: 14px;
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Scanner */
        .scanner-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            margin-bottom: 20px;
            display: none;
        }
        
        .scanner-container.active {
            display: block;
        }
        
        .scanner-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .scanner-header h3 {
            font-size: 14px;
            color: #34d399;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-close-scanner {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
        }
        
        #qr-reader {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
        }
        
        #qr-reader video {
            border-radius: 12px;
        }
        
        /* Asset Card */
        .asset-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            display: none;
        }
        
        .asset-card.active {
            display: block;
            animation: slideUp 0.4s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header-info p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .card-header-info h2 {
            color: white;
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            margin-top: 4px;
        }
        
        .card-header-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-header-icon i {
            font-size: 24px;
            color: white;
        }
        
        .card-body {
            padding: 24px;
            color: #1f2937;
        }
        
        .code-estado {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .code-badge {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            padding: 10px 16px;
            border-radius: 10px;
            font-family: monospace;
            font-weight: 700;
            font-size: 14px;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .estado-badge {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .estado-bueno {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .estado-regular {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        
        .estado-malo {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .info-section h3 {
            font-size: 11px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-item {
            margin-bottom: 14px;
        }
        
        .info-item label {
            font-size: 10px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            display: block;
            margin-bottom: 4px;
        }
        
        .info-item p {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }
        
        .financial-section {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-top: 24px;
        }
        
        .financial-section h3 {
            font-size: 11px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .financial-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        
        @media (max-width: 600px) {
            .financial-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .valor-contable {
            font-size: 20px !important;
            font-weight: 900 !important;
            color: #059669 !important;
        }
        
        .card-footer {
            background: #f9fafb;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .card-footer p {
            font-size: 10px;
            color: #9ca3af;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .btn-new-search {
            background: none;
            border: none;
            color: #3b82f6;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-new-search:hover {
            color: #2563eb;
        }
        
        /* Loading */
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(59, 130, 246, 0.2);
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .footer-text {
            text-align: center;
            color: rgba(255, 255, 255, 0.4);
            font-size: 12px;
            margin-top: 30px;
        }

        /* QR Reader custom styles */
        #qr-reader__scan_region {
            background: #1f2937 !important;
        }
        
        #qr-reader__dashboard_section_csr button {
            background: #3b82f6 !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 10px 20px !important;
            color: white !important;
            font-weight: 600 !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-icon">
                <i class="fas fa-qrcode"></i>
            </div>
            <h1>Verificar Activo</h1>
            <p>Sistema de Gestión de Activos Fijos</p>
        </div>

        <!-- Search Box -->
        <div class="search-box" id="searchBox">
            <label>Ingrese el Código de Inventario o Escanee el QR</label>
            <div class="search-row">
                <input type="text" class="search-input" id="codigoInput" 
                    placeholder="Ej: 123-004-007-000001"
                    onkeypress="if(event.key==='Enter') buscarActivo()">
                <button class="btn btn-primary" onclick="buscarActivo()">
                    <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-scan" id="btnScan" onclick="toggleScanner()">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            <p class="error-msg" id="errorMsg" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorText"></span>
            </p>
        </div>

        <!-- Scanner Container -->
        <div class="scanner-container" id="scannerContainer">
            <div class="scanner-header">
                <h3><i class="fas fa-camera"></i> Escáner de QR</h3>
                <button class="btn-close-scanner" onclick="stopScanner()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
            <div id="qr-reader"></div>
        </div>

        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Buscando activo...</p>
        </div>

        <!-- Asset Card -->
        <div class="asset-card" id="assetCard">
            <div class="card-header">
                <div class="card-header-info">
                    <p>Activo Verificado</p>
                    <h2 id="nombreActivo">-</h2>
                </div>
                <div class="card-header-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="card-body">
                <div class="code-estado">
                    <span class="code-badge" id="codigoInventario">-</span>
                    <span class="estado-badge" id="estadoBadge">-</span>
                </div>

                <div class="info-grid">
                    <!-- Technical Info -->
                    <div class="info-section">
                        <h3><i class="fas fa-info-circle"></i> Información Técnica</h3>
                        <div class="info-item">
                            <label>Marca / Modelo</label>
                            <p id="marcaModelo">-</p>
                        </div>
                        <div class="info-item">
                            <label>Número de Serie</label>
                            <p id="serie">-</p>
                        </div>
                        <div class="info-item">
                            <label>Categoría/Clasificación</label>
                            <p id="clasificacion">-</p>
                        </div>
                    </div>

                    <!-- Assignment Info -->
                    <div class="info-section">
                        <h3><i class="fas fa-user-check"></i> Asignación Actual</h3>
                        <div class="info-item">
                            <label>Unidad Administrativa / Área</label>
                            <p id="area">-</p>
                        </div>
                        <div class="info-item">
                            <label>Responsable del Bien</label>
                            <p id="responsable">-</p>
                        </div>
                        <div class="info-item">
                            <label>Ubicación</label>
                            <p id="ubicacion">-</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Section -->
                <div class="financial-section">
                    <h3><i class="fas fa-dollar-sign"></i> Datos Financieros y Adquisición</h3>
                    <div class="financial-grid">
                        <div class="info-item">
                            <label>Valor Contable</label>
                            <p class="valor-contable" id="valorContable">-</p>
                        </div>
                        <div class="info-item">
                            <label>Fecha de Ingreso</label>
                            <p id="fechaAdquisicion">-</p>
                        </div>
                        <div class="info-item">
                            <label>Fuente Fondos</label>
                            <p id="fuenteFondos">-</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <p>SGAF V2 • Verificación de Activo</p>
                <button class="btn-new-search" onclick="nuevaBusqueda()">
                    <i class="fas fa-search"></i> Nueva Búsqueda
                </button>
            </div>
        </div>

        <p class="footer-text">Sistema de Gestión de Activos Fijos • Alcaldía Municipal</p>
    </div>

    <script>
        let html5QrCode = null;
        let isScanning = false;

        // Check if code is in URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const codigo = urlParams.get('codigo');
            if (codigo) {
                document.getElementById('codigoInput').value = codigo;
                buscarActivo();
            }
        });

        function toggleScanner() {
            const container = document.getElementById('scannerContainer');
            const btn = document.getElementById('btnScan');
            
            if (isScanning) {
                stopScanner();
            } else {
                startScanner();
            }
        }

        function startScanner() {
            const container = document.getElementById('scannerContainer');
            const btn = document.getElementById('btnScan');
            
            container.classList.add('active');
            btn.classList.add('active');
            isScanning = true;
            
            html5QrCode = new Html5Qrcode("qr-reader");
            
            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText, decodedResult) => {
                    // QR scanned successfully
                    onQrScanned(decodedText);
                },
                (errorMessage) => {
                    // Ignore scan errors (no QR found yet)
                }
            ).catch((err) => {
                console.error("Error starting scanner:", err);
                showError("No se pudo acceder a la cámara. Verifique los permisos.");
                stopScanner();
            });
        }

        function stopScanner() {
            const container = document.getElementById('scannerContainer');
            const btn = document.getElementById('btnScan');
            
            if (html5QrCode && isScanning) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                }).catch(err => console.error(err));
            }
            
            container.classList.remove('active');
            btn.classList.remove('active');
            isScanning = false;
        }

        function onQrScanned(qrContent) {
            stopScanner();
            
            // Extract codigo from QR content
            // QR might contain just the code or a full URL
            let codigo = qrContent;
            
            // If it's a URL, extract the code
            if (qrContent.includes('verificar-activo')) {
                const match = qrContent.match(/verificar-activo[\/\?]?[codigo=]?(.+)/);
                if (match) {
                    codigo = match[1].replace('codigo=', '');
                }
            }
            
            document.getElementById('codigoInput').value = codigo;
            buscarActivo();
        }

        function buscarActivo() {
            const codigo = document.getElementById('codigoInput').value.trim();
            
            if (!codigo) {
                showError('Ingrese un código de inventario');
                return;
            }
            
            hideError();
            document.getElementById('searchBox').style.display = 'none';
            document.getElementById('assetCard').classList.remove('active');
            document.getElementById('loading').classList.add('active');
            
            fetch(`/api/assets/verify/${encodeURIComponent(codigo)}`)
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 404) {
                            throw new Error('Activo no encontrado con ese código');
                        }
                        throw new Error('Error al buscar el activo');
                    }
                    return response.json();
                })
                .then(asset => {
                    document.getElementById('loading').classList.remove('active');
                    showAsset(asset);
                })
                .catch(error => {
                    document.getElementById('loading').classList.remove('active');
                    document.getElementById('searchBox').style.display = 'block';
                    showError(error.message);
                });
        }

        function showAsset(asset) {
            document.getElementById('nombreActivo').textContent = asset.nombre_activo || 'Sin nombre';
            document.getElementById('codigoInventario').textContent = asset.codigo_inventario || '-';
            
            // Estado badge
            const estadoBadge = document.getElementById('estadoBadge');
            estadoBadge.textContent = asset.estado || 'N/A';
            estadoBadge.className = 'estado-badge';
            if (asset.estado === 'BUENO') estadoBadge.classList.add('estado-bueno');
            else if (asset.estado === 'REGULAR') estadoBadge.classList.add('estado-regular');
            else estadoBadge.classList.add('estado-malo');
            
            // Technical
            document.getElementById('marcaModelo').textContent = 
                (asset.marca || 'N/A') + ' • ' + (asset.modelo || 'N/A');
            document.getElementById('serie').textContent = asset.serie || 'SIN SERIE';
            document.getElementById('clasificacion').textContent = 
                asset.clasificacion?.nombre || 'N/A';
            
            // Assignment
            document.getElementById('area').textContent = asset.area?.nombre || 'NO ASIGNADO';
            document.getElementById('responsable').textContent = asset.personal 
                ? (asset.personal.nombre + ' ' + (asset.personal.apellido || '')).trim()
                : 'SIN ASIGNAR';
            document.getElementById('ubicacion').textContent = asset.ubicacion?.nombre || 'N/A';
            
            // Financial
            document.getElementById('valorContable').textContent = 
                'C$ ' + (parseFloat(asset.precio_adquisicion || 0).toLocaleString('es-NI', {minimumFractionDigits: 2}));
            document.getElementById('fechaAdquisicion').textContent = 
                asset.fecha_adquisicion ? formatDate(asset.fecha_adquisicion) : 'N/A';
            document.getElementById('fuenteFondos').textContent = 
                asset.fuente_financiamiento?.nombre || 'N/A';
            
            document.getElementById('assetCard').classList.add('active');
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-NI', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        function nuevaBusqueda() {
            document.getElementById('assetCard').classList.remove('active');
            document.getElementById('searchBox').style.display = 'block';
            document.getElementById('codigoInput').value = '';
            hideError();
        }

        function showError(message) {
            document.getElementById('errorText').textContent = message;
            document.getElementById('errorMsg').style.display = 'flex';
        }

        function hideError() {
            document.getElementById('errorMsg').style.display = 'none';
        }
    </script>
</body>
</html>
