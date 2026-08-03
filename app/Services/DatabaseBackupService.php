<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    protected string $disk;
    protected string $directory;

    public function __construct()
    {
        $this->disk = config('backup.disk');
        $this->directory = config('backup.directory');
    }

    /**
     * Run mysqldump and save the .sql file to storage. Returns the filename.
     */
    public function create(): string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $filename = 'abaan-backup-' . now()->format('Y-m-d_H-i-s') . '.sql';
        $path = Storage::disk($this->disk)->path("{$this->directory}/{$filename}");

        $this->ensureDirectoryExists();

        $command = [
            config('backup.mysqldump_path'),
            '--protocol=TCP',
            '--host=' . $config['host'],
            '--port=' . ($config['port'] ?? 3306),
            '--user=' . $config['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            $config['database'],
        ];

        $process = new Process($command);
        $process->setEnv([
            'MYSQL_PWD' => $config['password'] ?? '',
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
        ]);
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) use ($path) {
            if ($type === Process::OUT) {
                file_put_contents($path, $buffer, FILE_APPEND);
            }
        });

        if (! $process->isSuccessful()) {
            if (file_exists($path)) {
                unlink($path);
            }
            throw new ProcessFailedException($process);
        }

        $this->pruneOldBackups();

        return $filename;
    }

    /**
     * Restore the database from a given backup filename (must exist in the backups directory).
     */
    public function restore(string $filename): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $path = Storage::disk($this->disk)->path("{$this->directory}/{$filename}");

        if (! file_exists($path)) {
            throw new \RuntimeException('Backup file not found.');
        }

        $command = [
            config('backup.mysql_path'),
            '--protocol=TCP',
            '--host=' . $config['host'],
            '--port=' . ($config['port'] ?? 3306),
            '--user=' . $config['username'],
            $config['database'],
        ];

        $process = new Process($command);
        $process->setEnv([
            'MYSQL_PWD' => $config['password'] ?? '',
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
        ]);
        $process->setTimeout(300);
        $process->setInput(fopen($path, 'r'));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public function list(): array
    {
        $this->ensureDirectoryExists();

        $files = Storage::disk($this->disk)->files($this->directory);

        $backups = array_map(function ($file) {
            return [
                'filename' => basename($file),
                'size' => Storage::disk($this->disk)->size($file),
                'created_at' => Storage::disk($this->disk)->lastModified($file),
            ];
        }, $files);

        usort($backups, fn ($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $backups;
    }

    public function delete(string $filename): void
    {
        Storage::disk($this->disk)->delete("{$this->directory}/{$filename}");
    }

    public function storeUploaded(\Illuminate\Http\UploadedFile $file): string
    {
        $this->ensureDirectoryExists();

        $filename = 'uploaded-' . now()->format('Y-m-d_H-i-s') . '-' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', $file->getClientOriginalName());

        $file->storeAs($this->directory, $filename, $this->disk);

        return $filename;
    }

    protected function ensureDirectoryExists(): void
    {
        if (! Storage::disk($this->disk)->exists($this->directory)) {
            Storage::disk($this->disk)->makeDirectory($this->directory);
        }
    }

    protected function pruneOldBackups(): void
    {
        $max = config('backup.max_keep');
        $backups = $this->list();

        if (count($backups) > $max) {
            $toDelete = array_slice($backups, $max);
            foreach ($toDelete as $backup) {
                $this->delete($backup['filename']);
            }
        }
    }
}