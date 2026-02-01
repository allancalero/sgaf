# build-angular.ps1
# Script para construir y desplegar el frontend de Angular en Laravel

$FrontendDir = "frontend"
$PublicOutput = "public/SGAF2"
$Now = Get-Date -Format "HH:mm:ss"

Write-Host "[$Now] >>> Iniciando proceso de construcción de Angular..." -ForegroundColor Cyan

# 0. Verificar requisitos
if (-not (Get-Command "npm" -ErrorAction SilentlyContinue)) {
    Write-Error "ERROR: npm no está instalado o no se encuentra en el PATH."
    exit 1
}

# 1. Navegar al directorio del frontend
if (Test-Path $FrontendDir) {
    Push-Location $FrontendDir
} else {
    Write-Error "ERROR: No se encontró el directorio $FrontendDir"
    exit 1
}

# 2. Instalar dependencias si no existen
if (-not (Test-Path "node_modules")) {
    $Now = Get-Date -Format "HH:mm:ss"
    Write-Host "[$Now] >>> Instalando dependencias (esto puede tardar unos minutos)..." -ForegroundColor Yellow
    npm install
}

# 3. Construir la aplicación
$Now = Get-Date -Format "HH:mm:ss"
Write-Host "[$Now] >>> Ejecutando ng build..." -ForegroundColor Yellow
npx ng build --base-href /SGAF2/

if ($LASTEXITCODE -ne 0) {
    Write-Error "ERROR: Falló el build de Angular"
    Pop-Location
    exit 1
}

Pop-Location

# 4. Preparar el directorio público
$Now = Get-Date -Format "HH:mm:ss"
Write-Host "[$Now] >>> Desplegando archivos en $PublicOutput..." -ForegroundColor Cyan

if (-not (Test-Path $PublicOutput)) {
    New-Item -ItemType Directory -Path $PublicOutput -Force
} else {
    Write-Host "[$Now] >>> Limpiando archivos antiguos..." -ForegroundColor Gray
    Remove-Item "$PublicOutput/*" -Recurse -Force -ErrorAction SilentlyContinue
}

# 5. Copiar archivos (Estructura Angular 17+ dist/frontend/browser)
$BuildDir = "$FrontendDir/dist/frontend/browser"
if (-not (Test-Path $BuildDir)) {
    $BuildDir = "$FrontendDir/dist/frontend"
}

if (Test-Path $BuildDir) {
    Copy-Item "$BuildDir/*" $PublicOutput -Recurse -Force
    $Now = Get-Date -Format "HH:mm:ss"
    Write-Host "[$Now] >>> Despliegue completado con éxito." -ForegroundColor Green
} else {
    Write-Error "ERROR: No se encontraron los archivos compilados en $BuildDir"
    exit 1
}
