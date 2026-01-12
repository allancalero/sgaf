<?php
echo "System Temp Dir: " . sys_get_temp_dir() . "\n";
$temp = tmpfile();
if ($temp === false) {
    echo "tmpfile() failed!\n";
    $error = error_get_last();
    echo "Error: " . ($error['message'] ?? 'Unknown') . "\n";
} else {
    echo "tmpfile() succeeded.\n";
    $meta = stream_get_meta_data($temp);
    echo "Temp file path: " . $meta['uri'] . "\n";
    fwrite($temp, "test");
    fclose($temp);
}

// Check storage/app/backup-temp permissions
$backupTemp = __DIR__ . '/storage/app/backup-temp';
echo "Checking $backupTemp...\n";
if (!is_dir($backupTemp)) {
    echo "Directory does not exist.\n";
} else {
    echo "Directory exists.\n";
    if (is_writable($backupTemp)) {
        echo "Directory is writable.\n";
        $testFile = $backupTemp . '/test_permissions.txt';
        if (file_put_contents($testFile, 'test') === false) {
             echo "Failed to write to $testFile\n";
        } else {
             echo "Successfully wrote to $testFile\n";
             unlink($testFile);
        }
    } else {
        echo "Directory is NOT writable.\n";
    }
}
