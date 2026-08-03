<?php

namespace Modules\VideoDownloader\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\VideoDownloader\Exceptions\UnsupportedUrlException;
use Modules\VideoDownloader\Exceptions\VideoUnavailableException;
use Modules\VideoDownloader\Jobs\FetchVideoMetadataJob;
use Modules\VideoDownloader\Jobs\ProcessDownloadJob;
use Modules\VideoDownloader\Models\VdActivityLog;
use Modules\VideoDownloader\Models\VdDownload;
use Modules\VideoDownloader\Repositories\DownloadRepository;
use Modules\VideoDownloader\Requests\StartDownloadRequest;
use Modules\VideoDownloader\Requests\SubmitUrlRequest;
use Modules\VideoDownloader\Services\DownloadSettingService;
use Modules\VideoDownloader\Services\DownloadStatusService;
use Modules\VideoDownloader\Services\DownloadStorageService;
use Modules\VideoDownloader\Services\PlatformDetector;
use Modules\VideoDownloader\Services\VideoMetadataService;

class DownloadController extends Controller
{
    public function __construct(
        protected VideoMetadataService  $metadataService,
        protected DownloadRepository    $downloadRepo,
        protected DownloadStatusService $statusService,
        protected DownloadStorageService $storageService,
        protected DownloadSettingService $settingService,
        protected PlatformDetector      $platformDetector,
    ) {
    }

    // ── Step 1: New Download Page ─────────────────────────────────────────────

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('videodownloader.create-download'), 403);

        $setting          = $this->settingService->forCompany($request->user()->company_id);
        $activeDownloads  = $this->downloadRepo->countActive($request->user()->company_id);
        $atLimit          = $activeDownloads >= $setting->max_concurrent_downloads;
        $platforms        = config('videodownloader.platforms', []);

        return view('videodownloader::downloads.create', compact(
            'setting', 'activeDownloads', 'atLimit', 'platforms'
        ));
    }

    // ── Step 2: Fetch Metadata (AJAX) ─────────────────────────────────────────

    public function fetchMetadata(SubmitUrlRequest $request): JsonResponse
    {
        $url       = trim($request->url);
        $companyId = $request->user()->company_id;

        // Check concurrent download limit
        $setting         = $this->settingService->forCompany($companyId);
        $activeDownloads = $this->downloadRepo->countActive($companyId);

        if ($activeDownloads >= $setting->max_concurrent_downloads) {
            return response()->json([
                'success' => false,
                'message' => __('You have reached the maximum of :max concurrent downloads. Please wait for one to finish.', [
                    'max' => $setting->max_concurrent_downloads,
                ]),
            ], 429);
        }

        // Check platform restriction
        $platformKey = $this->platformDetector->detectKey($url);
        if (! $setting->isPlatformAllowed($platformKey)) {
            return response()->json([
                'success' => false,
                'message' => __('Downloads from :platform are not enabled for your account.', [
                    'platform' => ucfirst($platformKey),
                ]),
            ], 422);
        }

        try {
            $metadata = $this->metadataService->fetch($companyId, $url);

            // Build format groups for the UI
            $combined = $metadata->combinedFormats()->map->toArray()->values();
            $audio    = $metadata->audioFormats()->map->toArray()->values();

            return response()->json([
                'success'  => true,
                'metadata' => [
                    'title'        => $metadata->title,
                    'thumbnail'    => $metadata->thumbnailUrl,
                    'duration'     => $metadata->duration,
                    'uploader'     => $metadata->uploaderName,
                    'upload_date'  => $metadata->uploadDate,
                    'platform'     => $metadata->platform,
                    'platform_icon'=> $this->platformDetector->detect($url)->icon,
                ],
                'formats' => [
                    'video' => $combined,
                    'audio' => $setting->allow_audio_only ? $audio : [],
                ],
            ]);

        } catch (UnsupportedUrlException) {
            return response()->json([
                'success' => false,
                'message' => __('This URL is not supported. Please try a YouTube, TikTok, Instagram, or other supported link.'),
            ], 422);

        } catch (VideoUnavailableException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Could not retrieve video information. Please check the URL and try again.'),
            ], 500);
        }
    }

    // ── Step 3: Start Download ────────────────────────────────────────────────

    public function start(StartDownloadRequest $request): JsonResponse
    {
        $companyId = $request->user()->company_id;
        $url       = trim($request->url);

        // Detect platform
        $platformKey = $this->platformDetector->detectKey($url);

        // Create the download record
        $download = VdDownload::create([
            'company_id'          => $companyId,
            'user_id'             => $request->user()->id,
            'original_url'        => $url,
            'platform'            => $platformKey,
            'selected_format_id'  => $request->format_id,
            'selected_quality'    => $request->quality,
            'selected_format_ext' => $request->format_ext ?? 'mp4',
            'is_audio_only'       => $request->boolean('is_audio_only'),
            'status'              => VdDownload::STATUS_PENDING,
            'created_by'          => $request->user()->id,
            'updated_by'          => $request->user()->id,
        ]);

        // Fetch cached metadata to pre-fill title/thumbnail if available
        $cached = app(\Modules\VideoDownloader\Repositories\FormatCacheRepository::class)
            ->findValid($companyId, $url);

        if ($cached) {
            $download->update([
                'video_title'     => $cached->video_title,
                'video_thumbnail' => $cached->thumbnail_url,
                'video_duration'  => $cached->duration,
                'uploader_name'   => $cached->uploader_name,
                'upload_date'     => $cached->upload_date,
                'metadata_fetched_at' => now(),
            ]);
        }

        // Log the submission
        VdActivityLog::log($download, VdActivityLog::ACTION_SUBMITTED, [
            'url'       => $url,
            'format_id' => $request->format_id,
            'quality'   => $request->quality,
            'platform'  => $platformKey,
        ]);

        // Dispatch the download job
        ProcessDownloadJob::dispatch($download)
            ->onQueue(config('videodownloader.queues.downloads'));

        return response()->json([
            'success'     => true,
            'message'     => __('Download queued successfully.'),
            'download_id' => $download->id,
            'status_url'  => route('videodownloader.download.status', $download),
            'show_url'    => route('videodownloader.download.show', $download),
        ]);
    }

    // ── Download Detail Page ──────────────────────────────────────────────────

    public function show(Request $request, VdDownload $download): View
    {
        abort_unless($request->user()->can('view', $download), 403);

        $download->load('activityLogs.user');
        $fileExists = $this->storageService->fileExists($download);

        return view('videodownloader::downloads.show', compact('download', 'fileExists'));
    }

    // ── Status Polling (AJAX) ─────────────────────────────────────────────────

    public function status(Request $request, VdDownload $download): JsonResponse
    {
        abort_unless($request->user()->can('view', $download), 403);

        $download->refresh();

        return response()->json([
            'success'         => true,
            'status'          => $download->status,
            'badge_class'     => $download->status_badge_class,
            'status_icon'     => $download->status_icon,
            'error_message'   => $download->error_message,
            'file_size'       => $download->formatted_file_size,
            'completed_at'    => $download->completed_at?->format('d M Y, H:i'),
            'is_servable'     => $download->is_servable,
            'is_retryable'    => $download->is_retryable,
            'serve_url'       => $download->is_servable
                ? route('videodownloader.download.serve', $download)
                : null,
        ]);
    }

    // ── Serve File (Chunked Streaming) ────────────────────────────────────────

    public function serve(Request $request, VdDownload $download): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless($request->user()->can('serve', $download), 403);

        $disk = config('videodownloader.storage.disk', 'local');
        abort_unless(Storage::disk($disk)->exists($download->file_path), 404);

        // Log the file serve action
        VdActivityLog::log($download, VdActivityLog::ACTION_FILE_SERVED, [
            'user_id'   => $request->user()->id,
            'file_size' => $download->file_size,
        ]);

        $ext      = $download->selected_format_ext ?? pathinfo($download->file_path, PATHINFO_EXTENSION);
        $mimeType = $this->storageService->mimeForExt($ext);
        $fileName = $download->file_name ?? 'video.' . $ext;

        return response()->streamDownload(
            function () use ($disk, $download) {
                $stream = Storage::disk($disk)->readStream($download->file_path);
                while (! feof($stream)) {
                    echo fread($stream, 1024 * 64); // 64KB chunks
                    flush();
                }
                fclose($stream);
            },
            $fileName,
            [
                'Content-Type'   => $mimeType,
                'Content-Length' => $download->file_size,
            ]
        );
    }

    // ── Retry ─────────────────────────────────────────────────────────────────

    public function retry(Request $request, VdDownload $download): JsonResponse
    {
        abort_unless($request->user()->can('retry', $download), 403);

        $this->statusService->markPending($download);

        VdActivityLog::log($download, VdActivityLog::ACTION_DOWNLOAD_RETRIED, [
            'attempt' => $download->attempts,
        ]);

        ProcessDownloadJob::dispatch($download)
            ->onQueue(config('videodownloader.queues.downloads'));

        return response()->json([
            'success' => true,
            'message' => __('Download queued for retry.'),
        ]);
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    public function cancel(Request $request, VdDownload $download): JsonResponse
    {
        abort_unless($request->user()->can('cancel', $download), 403);

        $this->statusService->markCancelled($download);

        VdActivityLog::log($download, VdActivityLog::ACTION_DOWNLOAD_CANCELLED, [
            'cancelled_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Download cancelled.'),
        ]);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(Request $request, VdDownload $download): JsonResponse
    {
        abort_unless($request->user()->can('delete', $download), 403);

        // Delete the physical file first
        if ($download->file_path) {
            $this->storageService->deleteFile($download);

            VdActivityLog::log($download, VdActivityLog::ACTION_FILE_DELETED, [
                'deleted_by' => $request->user()->id,
                'file_size'  => $download->file_size,
            ]);
        }

        $download->delete();

        return response()->json([
            'success' => true,
            'message' => __('Download deleted.'),
        ]);
    }
}