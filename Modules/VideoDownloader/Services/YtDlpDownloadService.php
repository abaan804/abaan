<?php

namespace Modules\VideoDownloader\Services;

use Illuminate\Support\Facades\Log;
use Modules\VideoDownloader\Exceptions\DownloadFailedException;
use Modules\VideoDownloader\Exceptions\UnsupportedUrlException;
use Modules\VideoDownloader\Exceptions\VideoUnavailableException;
use Modules\VideoDownloader\Services\Contracts\VideoDownloadServiceInterface;
use Modules\VideoDownloader\Services\ValueObjects\DownloadResult;
use Modules\VideoDownloader\Services\ValueObjects\VideoMetadata;

class YtDlpDownloadService implements VideoDownloadServiceInterface
{
    protected string $binary;
    protected string $ffmpegDir;
    protected int    $timeout;

    public function __construct()
    {
        $this->binary    = config('videodownloader.ytdlp_binary', 'yt-dlp');
        $ffmpegBin       = config('videodownloader.ffmpeg_binary', 'ffmpeg');
        $this->ffmpegDir = dirname($ffmpegBin);
        $this->timeout   = 120; // seconds
    }

    // ── Metadata Fetch ────────────────────────────────────────────────────────

    public function fetchMetadata(string $url): VideoMetadata
    {
        $cmd = $this->buildCommand([
            '--dump-json',
            '--no-playlist',
            '--no-warnings',
            '--skip-download',
        ], $url);

        [$output, $stderr, $exitCode] = $this->exec($cmd, 30);

        if ($exitCode !== 0 || empty($output)) {
            $this->throwFromStderr($url, $stderr);
        }

        $data = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! isset($data['title'])) {
            throw new VideoUnavailableException($url, 'Could not parse video metadata.');
        }

        return VideoMetadata::fromYtDlp($data, $url);
    }

    // ── Download ──────────────────────────────────────────────────────────────

    public function download(string $url, string $formatId, string $destPath): DownloadResult
    {
        // Ensure destination directory exists
        $dir = dirname($destPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Build output template — yt-dlp adds the extension automatically
        $outputTemplate = $destPath;

        $cmd = $this->buildCommand([
            '-f', $formatId,
            '-o', $outputTemplate,
            '--no-playlist',
            '--no-warnings',
            '--merge-output-format', 'mp4',
            '--write-info-json',
            '--no-write-thumbnail',
        ], $url);

        [$output, $stderr, $exitCode] = $this->exec($cmd, $this->timeout);

        if ($exitCode !== 0) {
            $error = $this->extractError($stderr);
            Log::error('yt-dlp download failed', [
                'url'      => $url,
                'format'   => $formatId,
                'stderr'   => $stderr,
                'exitCode' => $exitCode,
            ]);
            return DownloadResult::failure($error);
        }

        // Find the output file (yt-dlp may append extension)
        $actualPath = $this->findOutputFile($destPath);

        if (! $actualPath || ! file_exists($actualPath)) {
            return DownloadResult::failure('Download completed but output file not found.');
        }

        $fileSize = filesize($actualPath);
        $fileName = basename($actualPath);

        // Clean up .info.json sidecar if present
        $infoJson = $destPath . '.info.json';
        if (file_exists($infoJson)) {
            @unlink($infoJson);
        }

        return DownloadResult::success($actualPath, $fileName, $fileSize);
    }

    // ── Support Check ─────────────────────────────────────────────────────────

    public function supports(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $cmd = $this->buildCommand([
            '--simulate',
            '--no-warnings',
            '--skip-download',
        ], $url);

        [, , $exitCode] = $this->exec($cmd, 15);

        return $exitCode === 0;
    }

    // ── Internal Helpers ──────────────────────────────────────────────────────

    /**
     * Build a full shell command string with proper escaping.
     */
    protected function buildCommand(array $args, string $url): string
    {
        $parts = ['"' . $this->binary . '"'];

        // Tell yt-dlp where ffmpeg lives
        if (! empty($this->ffmpegDir) && is_dir($this->ffmpegDir)) {
            $parts[] = '--ffmpeg-location';
            $parts[] = '"' . $this->ffmpegDir . '"';
        }

        foreach ($args as $arg) {
            // If it contains spaces and isn't already quoted, wrap it
            if (str_contains($arg, ' ') && ! str_starts_with($arg, '"')) {
                $parts[] = '"' . $arg . '"';
            } else {
                $parts[] = $arg;
            }
        }

        $parts[] = '"' . $url . '"';
        $parts[] = '2>&1';

        return implode(' ', $parts);
    }

    /**
     * Execute a shell command and return [stdout, stderr, exitCode].
     * Uses proc_open for reliable cross-platform execution on Windows.
     */
    protected function exec(string $command, int $timeout = 60): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (! is_resource($process)) {
            return ['', 'Failed to open process', 1];
        }

        fclose($pipes[0]);

        $stdout   = '';
        $stderr   = '';
        $start    = time();

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        while (true) {
            $status = proc_get_status($process);

            if (! $status['running']) {
                break;
            }

            if ((time() - $start) >= $timeout) {
                proc_terminate($process, 9);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                return ['', 'Process timed out after ' . $timeout . ' seconds.', 1];
            }

            $chunk = fread($pipes[1], 8192);
            if ($chunk !== false && $chunk !== '') {
                $stdout .= $chunk;
            }

            $errChunk = fread($pipes[2], 8192);
            if ($errChunk !== false && $errChunk !== '') {
                $stderr .= $errChunk;
            }

            usleep(100000); // 100ms poll
        }

        // Drain remaining output
        while ($chunk = fread($pipes[1], 8192)) $stdout .= $chunk;
        while ($chunk = fread($pipes[2], 8192)) $stderr .= $chunk;

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return [trim($stdout), trim($stderr), $exitCode];
    }

    /**
     * yt-dlp may output stdout to stderr combined (2>&1).
     * Find the actual output file by trying common extensions.
     */
    protected function findOutputFile(string $basePath): ?string
    {
        // Exact path first
        if (file_exists($basePath)) {
            return $basePath;
        }

        // Try common extensions yt-dlp may append
        $extensions = ['mp4', 'webm', 'mkv', 'm4a', 'mp3', 'ogg', 'opus'];
        foreach ($extensions as $ext) {
            $candidate = $basePath . '.' . $ext;
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        // Glob for anything with the base name
        $glob = glob(dirname($basePath) . '/' . basename($basePath) . '.*');
        if (! empty($glob)) {
            // Exclude .info.json files
            $files = array_filter($glob, fn ($f) => ! str_ends_with($f, '.info.json'));
            if (! empty($files)) {
                return array_values($files)[0];
            }
        }

        return null;
    }

    /**
     * Parse yt-dlp stderr and throw the appropriate exception.
     */
    protected function throwFromStderr(string $url, string $stderr): void
    {
        $lower = strtolower($stderr);

        if (str_contains($lower, 'private video') || str_contains($lower, 'private')) {
            throw new VideoUnavailableException($url, 'This video is private.');
        }

        if (str_contains($lower, 'age-restricted') || str_contains($lower, 'age restricted')) {
            throw new VideoUnavailableException($url, 'This video is age-restricted.');
        }

        if (str_contains($lower, 'video unavailable') || str_contains($lower, 'not available')) {
            throw new VideoUnavailableException($url, 'This video is not available.');
        }

        if (str_contains($lower, 'unsupported url') || str_contains($lower, 'no suitable extractor')) {
            throw new UnsupportedUrlException($url);
        }

        if (str_contains($lower, 'network') || str_contains($lower, 'connection')) {
            throw new VideoUnavailableException($url, 'Network error — could not reach the video.');
        }

        throw new VideoUnavailableException($url, $this->extractError($stderr));
    }

    /**
     * Extract the most meaningful line from yt-dlp stderr output.
     */
    protected function extractError(string $stderr): string
    {
        if (empty($stderr)) return 'Unknown error occurred.';

        // Last non-empty line is usually the most specific error
        $lines = array_filter(array_map('trim', explode("\n", $stderr)));
        $last  = end($lines);

        // Strip yt-dlp prefix like "ERROR: "
        return preg_replace('/^(ERROR|WARNING):\s*/i', '', $last ?: $stderr);
    }
}