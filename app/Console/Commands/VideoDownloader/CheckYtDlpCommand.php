<?php

namespace App\Console\Commands\VideoDownloader;

use Illuminate\Console\Command;

class CheckYtDlpCommand extends Command
{
    protected $signature = 'videodownloader:check-ytdlp
                            {--update : Update yt-dlp to the latest version}';

    protected $description = 'Check yt-dlp and ffmpeg binary availability and version';

    public function handle(): int
    {
        $ytdlp  = config('videodownloader.ytdlp_binary', 'C:/xampp/yt-dlp/yt-dlp.exe');
        $ffmpeg = config('videodownloader.ffmpeg_binary', 'C:/xampp/ffmpeg/bin/ffmpeg.exe');

        // ── Check yt-dlp ──────────────────────────────────────────────────────
        $this->info('Checking yt-dlp binary...');
        $this->line("  Path: {$ytdlp}");

        if (! file_exists($ytdlp)) {
            $this->error('yt-dlp binary not found at: ' . $ytdlp);
            $this->line('');
            $this->line('Download yt-dlp.exe from:');
            $this->line('  https://github.com/yt-dlp/yt-dlp/releases/latest');
            $this->line('Place it at: C:/xampp/yt-dlp/yt-dlp.exe');
            return Command::FAILURE;
        }

        $ytVersion = $this->execCommand('"' . $ytdlp . '" --version');
        if (empty($ytVersion)) {
            $this->error('yt-dlp found but could not retrieve version.');
            return Command::FAILURE;
        }

        $this->info("  Version: {$ytVersion}");
        $this->info('  Status: ✓ OK');

        // ── Check ffmpeg ──────────────────────────────────────────────────────
        $this->newLine();
        $this->info('Checking ffmpeg binary...');
        $this->line("  Path: {$ffmpeg}");

        if (file_exists($ffmpeg)) {
            // Found at configured path
            $ffVersion = $this->execCommand('"' . $ffmpeg . '" -version');
            $firstLine = strtok($ffVersion, "\n");
            $this->info("  Version: {$firstLine}");
            $this->info('  Status: ✓ OK');
        } else {
            // Try system PATH (Windows: where, Linux/Mac: which)
            $finder     = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
            $systemPath = trim($this->execCommand("{$finder} ffmpeg 2>NUL") ?? '');

            if ($systemPath && file_exists(strtok($systemPath, "\n"))) {
                $ffVersion = $this->execCommand('ffmpeg -version');
                $firstLine = strtok($ffVersion, "\n");
                $this->info("  Found in PATH: {$firstLine}");
                $this->warn('  Tip: Set FFMPEG_BINARY in .env to the explicit path for reliability.');
            } else {
                $this->warn('  ffmpeg: NOT FOUND at configured path or in system PATH.');
                $this->warn('  Audio+video merging will be limited for 720p+ formats.');
                $this->newLine();
                $this->line('  Fix options:');
                $this->line('  1. Set FFMPEG_BINARY=C:/xampp/ffmpeg/bin/ffmpeg.exe in .env');
                $this->line('     and confirm the file exists at that path.');
                $this->line('  2. Download ffmpeg from https://www.gyan.dev/ffmpeg/builds/');
                $this->line('     Extract to C:\\xampp\\ffmpeg\\');
                $this->line('     So that C:\\xampp\\ffmpeg\\bin\\ffmpeg.exe exists.');
            }
        }

        // ── Test yt-dlp + ffmpeg together ─────────────────────────────────────
        $this->newLine();
        $this->info('Testing yt-dlp format listing (short test)...');
        $testCmd = '"' . $ytdlp . '"'
            . ' --ffmpeg-location "' . dirname($ffmpeg) . '"'
            . ' -F "https://www.youtube.com/watch?v=BaW_jenozKc"'
            . ' --no-warnings 2>&1';

        $testOutput = $this->execCommand($testCmd, 15);

        if (str_contains($testOutput ?? '', 'ID') || str_contains($testOutput ?? '', 'format')) {
            $this->info('  Format listing: ✓ Working correctly');
        } else {
            $this->warn('  Format listing test skipped or returned unexpected output.');
            $this->line('  This is normal if you have no internet connection right now.');
        }

        // ── Update if requested ───────────────────────────────────────────────
        if ($this->option('update')) {
            $this->newLine();
            $this->info('Updating yt-dlp...');
            $updateOutput = $this->execCommand('"' . $ytdlp . '" -U');
            $this->line(trim($updateOutput ?? ''));
        }

        $this->newLine();
        $this->info('Setup check complete.');
        $this->line('Add to your .env if not already set:');
        $this->line('  YT_DLP_BINARY=C:/xampp/yt-dlp/yt-dlp.exe');
        $this->line('  FFMPEG_BINARY=C:/xampp/ffmpeg/bin/ffmpeg.exe');

        return Command::SUCCESS;
    }

    /**
     * Run a shell command and return trimmed output.
     * Uses proc_open for reliable cross-platform execution.
     */
    protected function execCommand(string $command, int $timeout = 10): string
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (! is_resource($process)) {
            return '';
        }

        fclose($pipes[0]);

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';
        $start  = time();

        while (true) {
            $status = proc_get_status($process);
            if (! $status['running']) break;
            if ((time() - $start) > $timeout) break;

            $chunk = fread($pipes[1], 4096);
            if ($chunk) $output .= $chunk;

            usleep(100000); // 100ms
        }

        // Read remaining output
        while ($chunk = fread($pipes[1], 4096)) {
            $output .= $chunk;
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return trim($output);
    }
}