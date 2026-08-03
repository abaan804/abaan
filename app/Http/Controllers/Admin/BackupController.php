<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(protected DatabaseBackupService $backupService)
    {
    }

    public function index(): View
    {
        $backups = $this->backupService->list();

        return view('admin.backups.index', compact('backups'));
    }

    public function create(): RedirectResponse
    {
        try {
            $filename = $this->backupService->create();
        } catch (\Throwable $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }

        return back()->with('success', "Backup created: {$filename}");
    }

    public function download(string $filename): StreamedResponse
    {
        $this->validateFilename($filename);

        $path = config('backup.directory') . '/' . $filename;

        abort_unless(Storage::disk(config('backup.disk'))->exists($path), 404);

        return Storage::disk(config('backup.disk'))->download($path);
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|file|max:512000', // 500MB max
        ]);

        $extension = $request->file('backup_file')->getClientOriginalExtension();

        if (! in_array(strtolower($extension), ['sql'])) {
            return back()->with('error', 'Only .sql backup files are supported for upload.');
        }

        $filename = $this->backupService->storeUploaded($request->file('backup_file'));

        return back()->with('success', "Backup uploaded: {$filename}. You can now restore it.");
    }

    public function restore(Request $request, string $filename): RedirectResponse
    {
        $this->validateFilename($filename);

        $request->validate([
            'confirm_text' => 'required|in:RESTORE',
        ]);

        try {
            $this->backupService->restore($filename);
        } catch (\Throwable $e) {
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }

        return back()->with('success', "Database restored from {$filename}.");
    }

    public function destroy(string $filename): RedirectResponse
    {
        $this->validateFilename($filename);

        $this->backupService->delete($filename);

        return back()->with('success', 'Backup deleted.');
    }

    /**
     * Prevent path traversal — only allow simple filenames, no slashes or dot-dot.
     */
    protected function validateFilename(string $filename): void
    {
        abort_if(
            str_contains($filename, '/') || str_contains($filename, '\\') || str_contains($filename, '..'),
            400,
            'Invalid filename.'
        );
    }
}