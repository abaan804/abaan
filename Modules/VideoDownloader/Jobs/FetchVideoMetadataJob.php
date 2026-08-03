<?php

namespace Modules\VideoDownloader\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\VideoDownloader\Exceptions\UnsupportedUrlException;
use Modules\VideoDownloader\Exceptions\VideoUnavailableException;
use Modules\VideoDownloader\Models\VdActivityLog;
use Modules\VideoDownloader\Models\VdDownload;
use Modules\VideoDownloader\Services\VideoMetadataService;

class FetchVideoMetadataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     * Metadata fetch is a fast read — 2 tries is sufficient.
     */
    public int $tries = 2;

    /**
     * Seconds to wait before retrying after a failure.
     */
    public int $backoff = 10;

    /**
     * Maximum seconds this job may run before being killed.
     * yt-dlp metadata fetch typically takes 2-10 seconds.
     */
    public int $timeout = 60;

    public function __construct(public VdDownload $download)
    {
    }

    public function handle(VideoMetadataService $metadataService): void
    {
        // Guard: only process pending downloads
        if ($this->download->status !== VdDownload::STATUS_PENDING) {
            Log::info("FetchVideoMetadataJob skipped — download #{$this->download->id} is not pending.");
            return;
        }

        try {
            $metadata = $metadataService->fetch(
                $this->download->company_id,
                $this->download->original_url
            );

            // Populate the download record with fetched metadata
            $this->download->update([
                'video_title'        => $metadata->title,
                'video_thumbnail'    => $metadata->thumbnailUrl,
                'video_duration'     => $metadata->duration,
                'uploader_name'      => $metadata->uploaderName,
                'upload_date'        => $metadata->uploadDate,
                'platform'           => $metadata->platform,
                'metadata_fetched_at'=> now(),
            ]);

            VdActivityLog::log($this->download, VdActivityLog::ACTION_METADATA_FETCHED, [
                'title'        => $metadata->title,
                'platform'     => $metadata->platform,
                'formats_count'=> $metadata->formats->count(),
            ]);

            Log::info("Metadata fetched for download #{$this->download->id}: {$metadata->title}");

        } catch (UnsupportedUrlException $e) {
            $this->failDownload(
                'This URL is not supported. Please try a YouTube, TikTok, Instagram, or other supported link.',
                VdActivityLog::ACTION_METADATA_FAILED,
                ['error' => $e->getMessage(), 'type' => 'unsupported_url']
            );

        } catch (VideoUnavailableException $e) {
            $this->failDownload(
                $e->getMessage(),
                VdActivityLog::ACTION_METADATA_FAILED,
                ['error' => $e->getMessage(), 'type' => 'unavailable']
            );

        } catch (\Throwable $e) {
            Log::error("FetchVideoMetadataJob failed for download #{$this->download->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // If we have retries left, let the queue retry automatically
            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            // All retries exhausted — mark as permanently failed
            $this->failDownload(
                'Could not retrieve video information. Please check the URL and try again.',
                VdActivityLog::ACTION_METADATA_FAILED,
                ['error' => $e->getMessage(), 'type' => 'unexpected']
            );
        }
    }

    /**
     * Called when all retries are exhausted and the job has permanently failed.
     */
    public function failed(\Throwable $e): void
    {
        Log::error("FetchVideoMetadataJob permanently failed for download #{$this->download->id}", [
            'error' => $e->getMessage(),
        ]);

        $this->download->refresh();

        if ($this->download->status === VdDownload::STATUS_PENDING) {
            $this->download->update([
                'status'        => VdDownload::STATUS_FAILED,
                'error_message' => 'Failed to retrieve video metadata after multiple attempts.',
            ]);

            VdActivityLog::log($this->download, VdActivityLog::ACTION_METADATA_FAILED, [
                'error' => $e->getMessage(),
                'final' => true,
            ]);
        }
    }

    protected function failDownload(string $message, string $action, array $properties = []): void
    {
        $this->download->update([
            'status'        => VdDownload::STATUS_FAILED,
            'error_message' => $message,
        ]);

        VdActivityLog::log($this->download, $action, $properties);
    }
}