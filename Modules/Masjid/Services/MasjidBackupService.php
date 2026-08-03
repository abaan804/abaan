<?php

namespace Modules\Masjid\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidPayment;
use Modules\Masjid\Models\MasjidPaymentAttachment;
use Modules\Masjid\Models\MasjidSeason;
use Modules\Masjid\Models\MasjidSeasonMember;
use Modules\Masjid\Models\MasjidSetting;

class MasjidBackupService
{
    /**
     * Current backup format version.
     * Increment this when the schema changes in a breaking way.
     */
    public const BACKUP_VERSION = '1.0';

    /**
     * Backup format versions this code can restore.
     */
    public const SUPPORTED_VERSIONS = ['1.0'];

    /**
     * Private disk path prefix for stored backups.
     */
    protected string $disk = 'local';

    // ─── CREATE ──────────────────────────────────────────────────────────────

    /**
     * Create a backup for a single mosque and store it server-side.
     * Returns the stored file path.
     */
    public function createForMosque(MasjidMosque $mosque): string
    {
        $data = $this->collectMosqueData($mosque);
        return $this->store($data, "mosque-{$mosque->id}");
    }

    /**
     * Create a full-module backup for an entire company (all mosques).
     * Returns the stored file path.
     */
    public function createForCompany(int $companyId): string
    {
        $mosques = MasjidMosque::where('company_id', $companyId)->get();

        $payload = [
            'version' => self::BACKUP_VERSION,
            'mode' => 'company',
            'company_id' => $companyId,
            'generated_at' => now()->toIso8601String(),
            'mosques' => $mosques->map(fn ($m) => $this->collectMosqueData($m, false))->values()->toArray(),
        ];

        return $this->store($payload, "company-{$companyId}");
    }

    /**
     * Collect all data for a single mosque into an array.
     */
    protected function collectMosqueData(MasjidMosque $mosque, bool $wrapVersion = true): array
    {
        $members = MasjidMember::withTrashed()->where('mosque_id', $mosque->id)->get();
        $seasons = MasjidSeason::withTrashed()->where('mosque_id', $mosque->id)->get();
        $seasonMembers = MasjidSeasonMember::where('mosque_id', $mosque->id)->get();
        $payments = MasjidPayment::withTrashed()->where('mosque_id', $mosque->id)->get();
        $attachments = MasjidPaymentAttachment::whereIn(
            'payment_id', $payments->pluck('id')
        )->get();
        $setting = MasjidSetting::where('mosque_id', $mosque->id)->first();

        $data = [
            'mosque' => $mosque->withoutRelations()->toArray(),
            'members' => $members->map->withoutRelations()->toArray(),
            'seasons' => $seasons->map->withoutRelations()->toArray(),
            'season_members' => $seasonMembers->map->withoutRelations()->toArray(),
            'payments' => $payments->map->withoutRelations()->toArray(),
            'payment_attachments' => $attachments->map->withoutRelations()->toArray(),
            'settings' => $setting?->withoutRelations()->toArray() ?? [],
            'counts' => [
                'members' => $members->count(),
                'seasons' => $seasons->count(),
                'season_members' => $seasonMembers->count(),
                'payments' => $payments->count(),
            ],
        ];

        if ($wrapVersion) {
            return array_merge([
                'version' => self::BACKUP_VERSION,
                'mode' => 'mosque',
                'mosque_id' => $mosque->id,
                'generated_at' => now()->toIso8601String(),
            ], $data);
        }

        return $data;
    }

    /**
     * Store the backup payload as a JSON file.
     * Returns the relative path inside the local disk.
     */
    protected function store(array $payload, string $prefix): string
    {
        $companyId = $payload['company_id'] ?? $payload['mosque']['company_id'] ?? 'unknown';
        $filename = "{$prefix}-" . now()->format('Y-m-d-His') . '-' . Str::random(6) . '.json';
        $path = "masjid-backups/{$companyId}/{$filename}";

        Storage::disk($this->disk)->put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }

    // ─── LIST ────────────────────────────────────────────────────────────────

    /**
     * List all stored backups for a company, newest first.
     */
    public function listForCompany(int $companyId): Collection
    {
        $dir = "masjid-backups/{$companyId}";

        if (! Storage::disk($this->disk)->exists($dir)) {
            return collect();
        }

        $files = Storage::disk($this->disk)->files($dir);

        return collect($files)
            ->map(function ($path) {
                $filename = basename($path);
                $size = Storage::disk($this->disk)->size($path);
                $modified = Storage::disk($this->disk)->lastModified($path);

                // Peek at first 300 chars to read the version/mode/generated_at
                // without loading the full (potentially large) JSON into memory.
                $handle = fopen(Storage::disk($this->disk)->path($path), 'r');
                $peek = fread($handle, 400);
                fclose($handle);

                $mode = str_contains($peek, '"mode":"mosque"') || str_contains($peek, '"mode": "mosque"')
                    ? 'mosque'
                    : 'company';

                preg_match('/"generated_at"\s*:\s*"([^"]+)"/', $peek, $dateMatch);
                $generatedAt = $dateMatch[1] ?? null;

                return [
                    'path' => $path,
                    'filename' => $filename,
                    'mode' => $mode,
                    'size' => $this->formatBytes($size),
                    'size_bytes' => $size,
                    'generated_at' => $generatedAt ? \Carbon\Carbon::parse($generatedAt) : null,
                    'modified_at' => \Carbon\Carbon::createFromTimestamp($modified),
                ];
            })
            ->sortByDesc('modified_at')
            ->values();
    }

    /**
     * Delete a stored backup file.
     */
    public function delete(int $companyId, string $filename): bool
    {
        $path = "masjid-backups/{$companyId}/{$filename}";

        // Safety: ensure path is within this company's directory
        if (! str_starts_with($path, "masjid-backups/{$companyId}/")) {
            return false;
        }

        return Storage::disk($this->disk)->delete($path);
    }

    // ─── RESTORE ─────────────────────────────────────────────────────────────

    /**
     * Restore from an uploaded or stored JSON file.
     * Returns ['success' => bool, 'message' => string, 'stats' => array].
     */
    public function restore(string $jsonContent, int $companyId): array
    {
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Invalid JSON file.', 'stats' => []];
        }

        // Version check
        if (! in_array($data['version'] ?? '', self::SUPPORTED_VERSIONS)) {
            return [
                'success' => false,
                'message' => "Unsupported backup version: {$data['version']}. Supported: " . implode(', ', self::SUPPORTED_VERSIONS),
                'stats' => [],
            ];
        }

        // Mode dispatch
        if (($data['mode'] ?? '') === 'company') {
            return $this->restoreCompany($data, $companyId);
        }

        return $this->restoreMosque($data, $companyId);
    }

    /**
     * Restore a single mosque backup.
     */
    protected function restoreMosque(array $data, int $companyId): array
    {
        // Security: ensure the backed-up mosque belongs to this company
        $mosqueData = $data['mosque'] ?? [];
        if (($mosqueData['company_id'] ?? null) != $companyId) {
            return ['success' => false, 'message' => 'This backup does not belong to your company.', 'stats' => []];
        }

        try {
            $stats = DB::transaction(function () use ($data, $companyId) {
                return $this->restoreSingleMosqueData($data, $companyId);
            });

            return ['success' => true, 'message' => 'Mosque restored successfully.', 'stats' => $stats];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Restore failed: ' . $e->getMessage(), 'stats' => []];
        }
    }

    /**
     * Restore a full company backup (multiple mosques).
     */
    protected function restoreCompany(array $data, int $companyId): array
    {
        if (($data['company_id'] ?? null) != $companyId) {
            return ['success' => false, 'message' => 'This backup does not belong to your company.', 'stats' => []];
        }

        $allStats = [];

        try {
            DB::transaction(function () use ($data, $companyId, &$allStats) {
                foreach ($data['mosques'] ?? [] as $mosqueData) {
                    // Wrap each mosque's data in the expected single-mosque format
                    $wrapped = array_merge(['version' => $data['version']], $mosqueData);
                    $stats = $this->restoreSingleMosqueData($wrapped, $companyId);
                    $allStats[] = $stats;
                }
            });

            return ['success' => true, 'message' => count($allStats) . ' mosque(s) restored successfully.', 'stats' => $allStats];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Restore failed: ' . $e->getMessage(), 'stats' => []];
        }
    }

    /**
     * Core restore logic for one mosque's data block.
     * Runs inside a DB transaction.
     * Returns stats array.
     */
    protected function restoreSingleMosqueData(array $data, int $companyId): array
    {
        $mosqueData = $data['mosque'];

        // ── Step 1: Upsert the mosque itself ─────────────────────────────
        $originalMosqueId = $mosqueData['id'];
        unset($mosqueData['id']);
        $mosqueData['company_id'] = $companyId;
        $mosqueData['updated_at'] = now();
        $mosqueData['deleted_at'] = null;

        $mosque = MasjidMosque::withTrashed()->updateOrCreate(
            ['company_id' => $companyId, 'mosque_name' => $mosqueData['mosque_name'], 'village_name' => $mosqueData['village_name']],
            $mosqueData
        );

        $newMosqueId = $mosque->id;

        // ── Step 2: Wipe existing data for this mosque (replace mode) ─────
        // Delete in reverse FK dependency order
        $existingPaymentIds = MasjidPayment::withTrashed()->where('mosque_id', $newMosqueId)->pluck('id');
        MasjidPaymentAttachment::whereIn('payment_id', $existingPaymentIds)->delete();
        MasjidPayment::withTrashed()->where('mosque_id', $newMosqueId)->forceDelete();
        MasjidSeasonMember::where('mosque_id', $newMosqueId)->forceDelete();
        MasjidSeason::withTrashed()->where('mosque_id', $newMosqueId)->forceDelete();
        MasjidMember::withTrashed()->where('mosque_id', $newMosqueId)->forceDelete();
        MasjidSetting::where('mosque_id', $newMosqueId)->delete();

        // ── Step 3: Restore members — build old_id → new_id map ──────────
        $memberIdMap = [];
        foreach ($data['members'] ?? [] as $row) {
            $oldId = $row['id'];
            unset($row['id']);
            $row['mosque_id'] = $newMosqueId;
            $row['company_id'] = $companyId;
            $member = MasjidMember::withTrashed()->create($row);
            $memberIdMap[$oldId] = $member->id;
        }

        // ── Step 4: Restore seasons — build old_id → new_id map ──────────
        $seasonIdMap = [];
        foreach ($data['seasons'] ?? [] as $row) {
            $oldId = $row['id'];
            unset($row['id']);
            $row['mosque_id'] = $newMosqueId;
            $row['company_id'] = $companyId;
            $season = MasjidSeason::withTrashed()->create($row);
            $seasonIdMap[$oldId] = $season->id;
        }

        // ── Step 5: Restore season_members — remap FKs ───────────────────
        $seasonMemberIdMap = [];
        foreach ($data['season_members'] ?? [] as $row) {
            $oldId = $row['id'];
            unset($row['id']);
            $row['mosque_id'] = $newMosqueId;
            $row['company_id'] = $companyId;
            $row['member_id'] = $memberIdMap[$row['member_id']] ?? null;
            $row['season_id'] = $seasonIdMap[$row['season_id']] ?? null;

            if (! $row['member_id'] || ! $row['season_id']) continue;

            $sm = MasjidSeasonMember::create($row);
            $seasonMemberIdMap[$oldId] = $sm->id;
        }

        // ── Step 6: Restore payments — remap FKs, skip receipt_no ────────
        $paymentIdMap = [];
        foreach ($data['payments'] ?? [] as $row) {
            $oldId = $row['id'];
            unset($row['id']);
            $row['mosque_id'] = $newMosqueId;
            $row['company_id'] = $companyId;
            $row['member_id'] = $memberIdMap[$row['member_id']] ?? null;
            $row['season_id'] = $seasonIdMap[$row['season_id']] ?? null;
            $row['season_member_id'] = $seasonMemberIdMap[$row['season_member_id']] ?? null;

            if (! $row['member_id'] || ! $row['season_id'] || ! $row['season_member_id']) continue;

            // Keep original receipt_no — it's a meaningful reference number,
            // not a DB auto-increment, so it's safe to preserve as-is.
            $payment = MasjidPayment::withTrashed()->create($row);
            $paymentIdMap[$oldId] = $payment->id;
        }

        // ── Step 7: Restore payment attachments — metadata only ───────────
        // File binaries are NOT stored in the backup; attachment metadata is restored
        // so the record exists but the file link may be broken if storage was not migrated.
        foreach ($data['payment_attachments'] ?? [] as $row) {
            unset($row['id']);
            $row['payment_id'] = $paymentIdMap[$row['payment_id']] ?? null;
            if (! $row['payment_id']) continue;
            MasjidPaymentAttachment::create($row);
        }

        // ── Step 8: Restore settings ──────────────────────────────────────
        if (! empty($data['settings'])) {
            $settings = $data['settings'];
            unset($settings['id']);
            $settings['mosque_id'] = $newMosqueId;
            $settings['company_id'] = $companyId;
            MasjidSetting::create($settings);
        }

        // ── Step 9: Recalculate all season_member cached totals ───────────
        // Since we restored raw amount_paid/status from the backup, verify consistency
        foreach ($seasonMemberIdMap as $newSmId) {
            $sm = MasjidSeasonMember::find($newSmId);
            $sm?->recalculate();
        }

        return [
            'mosque' => $mosque->mosque_name,
            'members' => count($memberIdMap),
            'seasons' => count($seasonIdMap),
            'season_members' => count($seasonMemberIdMap),
            'payments' => count($paymentIdMap),
        ];
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    public function getStoredPath(int $companyId, string $filename): ?string
    {
        $path = "masjid-backups/{$companyId}/{$filename}";
        return Storage::disk($this->disk)->exists($path) ? $path : null;
    }

    public function getContent(string $path): string
    {
        return Storage::disk($this->disk)->get($path);
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}