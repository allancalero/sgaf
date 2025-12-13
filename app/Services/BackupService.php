<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use Exception;

class BackupService
{
    /**
     * Create a new database backup.
     */
    public function createBackup(string $type = 'manual', ?int $userId = null): Backup
    {
        $timestamp = now()->format('Ymd_His');
        $filename = "backup_{$timestamp}.sql";
        $path = "backups/{$filename}";
        
        // Create backup record
        $backup = Backup::create([
            'filename' => $filename,
            'path' => $path,
            'type' => $type,
            'status' => 'in_progress',
            'created_by' => $userId,
        ]);

        try {
            // Get database configuration
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            // Build mysqldump command
            $dumpPath = storage_path("app/{$path}");
            
            // Ensure backups directory exists
            Storage::disk('local')->makeDirectory('backups');

            // Detect mysqldump executable (Windows/XAMPP compatibility)
            $mysqldump = str_replace("'", '', $this->getMysqldumpPath()); // Remove quotes

            // Build mysqldump command
            $command = sprintf(
                '"%s" --user=%s --password=%s --host=%s --port=%s %s',
                $mysqldump,
                $username,
                $password,
                $host,
                $port,
                $database
            );

            // Log the command for debugging
            \Log::info('Backup command:', ['command' => $command, 'mysqldump_path' => $mysqldump]);

            // Execute command using proc_open for better control
            $descriptorspec = [
                0 => ['pipe', 'r'],  // stdin
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w'],  // stderr
            ];

            $process = proc_open($command, $descriptorspec, $pipes);

            if (!is_resource($process)) {
                \Log::error('Failed to start mysqldump process');
                throw new Exception('Failed to start mysqldump process');
            }

            // Close stdin
            fclose($pipes[0]);

            // Read stdout (the SQL dump)
            $sqlDump = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // Read stderr (errors)
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // Get exit code
            $exitCode = proc_close($process);

            // Log detailed information
            \Log::info('Mysqldump execution result:', [
                'exit_code' => $exitCode,
                'output_length' => strlen($sqlDump),
                'has_errors' => !empty($errors),
                'errors' => $errors,
            ]);

            if ($exitCode !== 0 || empty($sqlDump)) {
                $errorMsg = 'Mysqldump failed. Exit code: ' . $exitCode . '. Errors: ' . $errors . '. Output length: ' . strlen($sqlDump);
                \Log::error($errorMsg);
                throw new Exception($errorMsg);
            }

            // Write SQL dump to file
            Storage::disk('local')->put($path, $sqlDump);

            // Get file size
            $size = Storage::disk('local')->size($path);

            \Log::info('Backup created successfully:', ['path' => $path, 'size' => $size]);

            // Update backup record
            $backup->update([
                'size' => $size,
                'status' => 'completed',
            ]);

            return $backup;

        } catch (Exception $e) {
            $backup->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Get all backups ordered by creation date.
     */
    public function listBackups()
    {
        return Backup::with('creator')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($backup) {
                return [
                    'id' => $backup->id,
                    'filename' => $backup->filename,
                    'size' => $backup->size,
                    'human_size' => $backup->human_size,
                    'type' => $backup->type,
                    'status' => $backup->status,
                    'created_by' => $backup->creator ? $backup->creator->nombre . ' ' . $backup->creator->apellido : 'Sistema',
                    'created_at' => $backup->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    /**
     * Download a backup file.
     */
    public function downloadBackup(int $backupId)
    {
        $backup = Backup::findOrFail($backupId);

        if (!Storage::disk('local')->exists($backup->path)) {
            throw new Exception('Backup file not found.');
        }

        return Storage::disk('local')->download($backup->path, $backup->filename);
    }

    /**
     * Restore database from a backup.
     */
    public function restoreBackup(int $backupId): void
    {
        $backup = Backup::findOrFail($backupId);

        if ($backup->status !== 'completed') {
            throw new Exception('Cannot restore from incomplete backup.');
        }

        if (!Storage::disk('local')->exists($backup->path)) {
            throw new Exception('Backup file not found.');
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        $backupPath = storage_path("app/{$backup->path}");

        // Detect mysql executable (Windows/XAMPP compatibility)
        $mysql = $this->getMysqlPath();

        // Execute mysql restore
        $command = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s %s < %s',
            $mysql,
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );

        $result = Process::run($command);

        if ($result->failed()) {
            throw new Exception('Database restore failed: ' . $result->errorOutput());
        }
    }

    /**
     * Delete a backup.
     */
    public function deleteBackup(int $backupId): void
    {
        $backup = Backup::findOrFail($backupId);
        $backup->delete(); // File will be deleted automatically via model event
    }

    /**
     * Clean old backups.
     */
    public function cleanOldBackups(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        
        $oldBackups = Backup::where('created_at', '<', $cutoffDate)->get();
        
        foreach ($oldBackups as $backup) {
            $backup->delete();
        }

        return $oldBackups->count();
    }

    /**
     * Get mysqldump executable path (Windows/XAMPP compatibility).
     */
    private function getMysqldumpPath(): string
    {
        // Check common XAMPP locations on Windows
        $xamppPaths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump',
        ];

        foreach ($xamppPaths as $path) {
            if (file_exists($path)) {
                return escapeshellarg($path);
            }
        }

        // Fallback to system PATH
        return 'mysqldump';
    }

    /**
     * Get mysql executable path (Windows/XAMPP compatibility).
     */
    private function getMysqlPath(): string
    {
        // Check common XAMPP locations on Windows
        $xamppPaths = [
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'C:\\xampp\\mysql\\bin\\mysql',
        ];

        foreach ($xamppPaths as $path) {
            if (file_exists($path)) {
                return escapeshellarg($path);
            }
        }

        // Fallback to system PATH
        return 'mysql';
    }
}
