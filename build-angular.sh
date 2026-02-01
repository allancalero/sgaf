#!/bin/bash
# build-angular.sh
# Script para construir y desplegar el frontend de Angular en Laravel (Linux/macOS)

FRONTEND_DIR="frontend"
PUBLIC_OUTPUT="public/SGAF2"
NOW=$(date +"%H:%M:%S")

echo "[$NOW] >>> Iniciando proceso de construcción de Angular..."

# 0. Verificar requisitos
if ! command -v npm &> /dev/null
then
    echo "ERROR: npm no está instalado o no se encuentra en el PATH."
    exit 1
fi

# 1. Navegar al directorio del frontend
if [ -d "$FRONTEND_DIR" ]; then
    cd "$FRONTEND_DIR"
else
    echo "ERROR: No se encontró el directorio $FRONTEND_DIR"
    exit 1
fi

# 2. Instalar dependencias si no existen
if [ ! -d "node_modules" ]; then
    NOW=$(date +"%H:%M:%S")
    echo "[$NOW] >>> Instalando dependencias (esto puede tardar unos minutos)..."
    npm install
fi

# 3. Construir la aplicación
NOW=$(date +"%H:%M:%S")
echo "[$NOW] >>> Ejecutando ng build..."
npx ng build --base-href /SGAF2/

if [ $? -ne 0 ]; then
    echo "ERROR: Falló el build de Angular"
    exit 1
fi

cd ..

# 4. Preparar el directorio público
NOW=$(date +"%H:%M:%S")
echo "[$NOW] >>> Desplegando archivos en $PUBLIC_OUTPUT..."

if [ ! -d "$PUBLIC_OUTPUT" ]; then
    mkdir -p "$PUBLIC_OUTPUT"
else
    echo "[$NOW] >>> Limpiando archivos antiguos..."
    rm -rf "${PUBLIC_OUTPUT:?}"/*
fi

# 5. Copiar archivos (Estructura Angular 17+ dist/frontend/browser)
BUILD_DIR="$FRONTEND_DIR/dist/frontend/browser"
if [ ! -d "$BUILD_DIR" ]; then
    BUILD_DIR="$FRONTEND_DIR/dist/frontend"
fi

if [ -d "$BUILD_DIR" ]; then
    cp -r "$BUILD_DIR"/* "$PUBLIC_OUTPUT"
    NOW=$(date +"%H:%M:%S")
    echo "[$NOW] >>> Despliegue completado con éxito."
else
    echo "ERROR: No se encontraron los archivos compilados en $BUILD_DIR"
    exit 1
fi
