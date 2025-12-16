<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'C:\Users\IT\Desktop\INVENTARIO ALCALDIA DE TIPITAPA AL 23 -04-25.xlsx';

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName('INVENTARIO GENERAL');
    
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    
    echo "=== INVENTARIO GENERAL ===\n";
    echo "Total filas: {$highestRow}\n\n";
    
    // Leer headers desde fila 2
    echo "COLUMNAS (Headers en fila 2):\n";
    $headers = [];
    $colIndex = 0;
    for ($col = 'A'; $col <= 'Z'; $col++) {
        $value = trim($sheet->getCell($col . '2')->getValue() ?? '');
        if (!empty($value)) {
            $headers[$col] = $value;
            echo "  {$col}: {$value}\n";
            $colIndex++;
        }
    }
    
    // Muestra de datos (filas 3-7)
    echo "\n=== MUESTRA DE DATOS (filas 3-7) ===\n";
    for ($row = 3; $row <= min(7, $highestRow); $row++) {
        echo "\n--- Fila {$row} ---\n";
        foreach ($headers as $col => $headerName) {
            $cellValue = $sheet->getCell($col . $row)->getValue();
            if ($cellValue !== null && $cellValue !== '') {
                echo "  [{$headerName}]: {$cellValue}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
