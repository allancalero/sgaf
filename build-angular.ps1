# build-angular.ps1
# Script para construir y desplegar el frontend de Angular en Laravel

$FrontendDir = "frontend"
$PublicOutput = "public/SGAF2"

Write-Host ">>> Iniciando proceso de construcción de Angular..." -ForegroundColor Cyan

# 1. Navegar al directorio del frontend
if (Test-Path $FrontendDir) {
    Push-Location $FrontendDir
} else {
    Write-Error "No se encontró el directorio $FrontendDir"
    exit 1
}

# 2. Instalar dependencias si no existen
if (-not (Test-Path "node_modules")) {
    Write-Host ">>> Instalando dependencias (esto puede tardar unos minutos)..." -ForegroundColor Yellow
    npm install
}

# 3. Construir la aplicación
Write-Host ">>> Ejecutando ng build..." -ForegroundColor Yellow
npx ng build --base-href /SGAF2/

if ($LASTEXITCODE -ne 0) {
    Write-Error "Error durante el build de Angular"
    Pop-Location
    exit 1
}

Pop-Location

# 4. Preparar el directorio público
Write-Host ">>> Desplegando archivos en $PublicOutput..." -ForegroundColor Cyan

if (-not (Test-Path $PublicOutput)) {
    New-Item -ItemType Directory -Path $PublicOutput -Force
} else {
    # Limpiar solo archivos, mantener carpeta por si acaso
    Remove-Item "$PublicOutput/*" -Recurse -Force
}

# 5. Copiar archivos (asumiendo estructura dist/frontend/browser)
$BuildDir = "$FrontendDir/dist/frontend/browser"
if (-not (Test-Path $BuildDir)) {
    # Intentar sin /browser por si acaso es una versión antigua de Angular
    $BuildDir = "$FrontendDir/dist/frontend"
}

if (Test-Path $BuildDir) {
    Copy-Item "$BuildDir/*" $PublicOutput -Recurse -Force
    Write-Host ">>> Despliegue completado con éxito." -ForegroundColor Green
} else {
    Write-Error "No se encontraron los archivos compilados en $BuildDir"
    exit 1
}
