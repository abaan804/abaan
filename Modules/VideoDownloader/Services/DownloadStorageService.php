<?php

namespace Modules\VideoDownloader\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\VideoDownloader\Models\VdDownload;

class DownloadStorageService
{
    protected string $disk;
    protected string $basePath;

    public function __construct()
    {
        $this->disk     = config('videodownloader.storage.disk', 'local');
        $this->basePath = config('videodownloader.storage.base_path', 'video-downloads');
    }

    /**
     * Build the absolute temp path where yt-dlp should write the file.
     * The extension is omitted — yt-dlp appends it automatically.
     */
    public function buildTempPath(VdDownload $download): string
    {
        $dir = Storage::disk($this->disk)->path(
            config('videodownloader.storage.temp_path', 'video-downloads/temp')
        );

        if (! is_dir($dir)) mkdir($dir, 0755, true);

        return $dir . DIRECTORY_SEPARATOR . 'dl_' . $download->id . '_' . Str::random(8);
    }

    /**
     * Move the downloaded temp file to its permanent location.
     * Returns the relative storage path.
     */
    public function moveToFinal(VdDownload $download, string $absoluteTempPath): string
    {
        $ext         = pathinfo($absoluteTempPath, PATHINFO_EXTENSION);
        $safeTitle   = $this->sanitizeFilename($download->video_title ?? 'video');
        $fileName    = $safeTitle . '.' . $ext;
        $relativeDest = implode('/', [
            $this->basePath,
            $download->company_id,
            $download->user_id,
            $download->id,
            $fileName,
        ]);

        // Create destination directory
        $absDir = Storage::disk($this->disk)->path(
            dirname($relativeDest)
        );
        if (! is_dir($absDir)) mkdir($absDir, 0755, true);

        $absDest = Storage::disk($this->disk)->path($relativeDest);
        rename($absoluteTempPath, $absDest);

        return $relativeDest;
    }

    /**
     * Delete the physical file for a download record.
     */
    public function deleteFile(VdDownload $download): bool
    {
        if (! $download->file_path) return false;

        try {
            if (Storage::disk($this->disk)->exists($download->file_path)) {
                Storage::disk($this->disk)->delete($download->file_path);
            }

            // Remove the directory if empty
            $dir = dirname($download->file_path);
            $absDir = Storage::disk($this->disk)->path($dir);
            if (is_dir($absDir) && count(scandir($absDir)) === 2) {
                rmdir($absDir);
            }

            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Could not delete download file', [
                'path'  => $download->file_path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get the absolute path to a stored file.
     */
    public function absolutePath(string $relativePath): string
    {
        return Storage::disk($this->disk)->path($relativePath);
    }

    /**
     * Whether the file physically exists on disk.
     */
    public function fileExists(VdDownload $download): bool
    {
        if (! $download->file_path) return false;
        return Storage::disk($this->disk)->exists($download->file_path);
    }

    /**
     * Total storage used by a company in bytes.
     */
    public function companyStorageUsed(int $companyId): int
    {
        $dir = $this->basePath . '/' . $companyId;

        if (! Storage::disk($this->disk)->exists($dir)) return 0;

        $files = Storage::disk($this->disk)->allFiles($dir);
        $total = 0;
        foreach ($files as $file) {
            $total += Storage::disk($this->disk)->size($file);
        }

        return $total;
    }

    /**
     * Sanitize a string for use as a filename.
     * Removes path separators, null bytes, and limits length.
     */
    public function sanitizeFilename(string $name): string
    {
        // Remove illegal characters
        $name = preg_replace('/[\/\\\:*?"<>|]/', '', $name);
        // Replace whitespace runs with single underscore
        $name = preg_replace('/\s+/', '_', $name);
        // Remove null bytes
        $name = str_replace("\0", '', $name);
        // Limit to 100 characters
        $name = Str::limit($name, 100, '');

        return empty($name) ? 'video' : $name;
    }

    /**
     * MIME type for a file extension.
     */
    public function mimeForExt(string $ext): string
    {
        return match (strtolower($ext)) {
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'mkv'  => 'video/x-matroska',
            'mp3'  => 'audio/mpeg',
            'm4a'  => 'audio/mp4',
            'ogg'  => 'audio/ogg',
            'opus' => 'audio/opus',
            default => 'application/octet-stream',
        };
    }
}