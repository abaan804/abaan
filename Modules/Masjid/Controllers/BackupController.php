<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Services\MasjidBackupService;

class BackupController extends Controller
{
    public function __construct(protected MasjidBackupService $backupService)
    {
    }

    // ─── INDEX ────────────────────────────────────────────────────────────────

    public function index(Request $request, MasjidMosque $mosque): View
    {
        abort_unless(
            $request->user()->can('masjid.manage-settings')
            && $mosque->company_id === $request->user()->company_id,
            403
        );

        $backups = $this->backupService->listForCompany($request->user()->company_id);

        return view('masjid::backups.index', compact('mosque', 'backups'));
    }

    // ─── CREATE ───────────────────────────────────────────────────────────────

    public function createMosqueBackup(Request $request, MasjidMosque $mosque): JsonResponse
    {
        abort_unless(
            $request->user()->can('masjid.manage-settings')
            && $mosque->company_id === $request->user()->company_id,
            403
        );

        try {
            $path = $this->backupService->createForMosque($mosque);
            $filename = basename($path);

            return response()->json([
                'success' => true,
                'message' => __('Mosque backup created: :file', ['file' => $filename]),
                'filename' => $filename,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Backup failed: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    public function createCompanyBackup(Request $request, MasjidMosque $mosque): JsonResponse
    {
        abort_unless(
            $request->user()->can('masjid.manage-settings')
            && $mosque->company_id === $request->user()->company_id,
            403
        );

        try {
            $path = $this->backupService->createForCompany($request->user()->company_id);
            $filename = basename($path);

            return response()->json([
                'success' => true,
                'message' => __('Full module backup created: :file', ['file' => $filename]),
                'filename' => $filename,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Backup failed: :error', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    // ─── DOWNLOAD ─────────────────────────────────────────────────────────────

    public function download(Request $request, MasjidMosque $mosque, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(
            $request->user()->can('masjid.manage-settings')
            && $mosque->company_id === $request->user()->company_id,
            403
        );

        // Sanitise filename — allow only safe characters
        $filename = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '', $filename);
        $path = $this->backupService->getStoredPath($request->user()->company_id, $filename);
        abort_if(! $path, 404);

        return Storage::disk('local')->download($path, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    // ─── DELETE STORED BACKUP ─────────────────────────────────────────────────

    public function destroy(Request $request, MasjidMosque $mosque, string $filename): JsonResponse
    {
        abort_unless(
            $request->user()->can('masjid.manage-settings')
            && $mosque->company_id === $request->user()->company_id,
            403
        );

        $filename = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '', $filename);
        $deleted = $this->backupService->delete($request->user()->company_id, $filename);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? __('Backup deleted.') : __('File not found.'),
        ]);
    }

    // ─── UPLOAD & RESTORE ─────────────────────────────────────────────────────

    public function upload(Request $request, MasjidMosque $mosque): JsonResponse
    {
        abort_unless(
            $request->user()->can('masjid.manage-settings')
            && $mosque->company_id === $request->user()->company_id,
            403
        );

        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:51200', // 50MB max
            'confirm_name' => 'required|string',
        ]);

        // Confirm name check — user must type the mosque name exactly
        if (trim($request->confirm_name) !== $mosque->mosque_name) {
            return response()->json([
                'success' => false,
                'message' => __('Mosque name confirmation does not match. Restore cancelled.'),
            ], 422);
        }

        $jsonContent = file_get_contents($request->file('backup_file')->getRealPath());
        $result = $this->backupService->restore($jsonContent, $request->user()->company_id);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'stats' => $result['stats'],
        ], $result['success'] ? 200 : 422);
    }

    /**
     * Restore directly from a stored server-side backup (no upload needed).
     */
    public function restoreStored(Request $request, MasjidMosque $mosque, string $filename): JsonResponse
    {
        abort_unless(
            $request->user()->can('masjid.manage-settings')
            && $mosque->company_id === $request->user()->company_id,
            403
        );

        $request->validate([
            'confirm_name' => 'required|string',
        ]);

        if (trim($request->confirm_name) !== $mosque->mosque_name) {
            return response()->json([
                'success' => false,
                'message' => __('Mosque name confirmation does not match. Restore cancelled.'),
            ], 422);
        }

        $filename = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '', $filename);
        $path = $this->backupService->getStoredPath($request->user()->company_id, $filename);

        if (! $path) {
            return response()->json(['success' => false, 'message' => __('Backup file not found.')], 404);
        }

        $jsonContent = $this->backupService->getContent($path);
        $result = $this->backupService->restore($jsonContent, $request->user()->company_id);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'stats' => $result['stats'],
        ], $result['success'] ? 200 : 422);
    }
}