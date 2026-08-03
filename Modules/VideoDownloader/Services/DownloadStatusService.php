<?php

namespace Modules\VideoDownloader\Services;

use Modules\VideoDownloader\Models\VdDownload;

class DownloadStatusService
{
    /**
     * Transition a download to a new status.
     * Throws if the transition is not allowed by the state machine.
     */
    public function transition(
        VdDownload $download,
        string     $newStatus,
        array      $extra = []
    ): VdDownload {
        if (! $download->canTransitionTo($newStatus)) {
            throw new \LogicException(sprintf(
                'Cannot transition download #%d from [%s] to [%s].',
                $download->id,
                $download->status,
                $newStatus
            ));
        }

        $data = array_merge(['status' => $newStatus], $extra);

        if ($newStatus === VdDownload::STATUS_PROCESSING) {
            $data['download_started_at'] = now();
            $data['last_attempted_at']   = now();
            $data['attempts']            = $download->attempts + 1;
        }

        if ($newStatus === VdDownload::STATUS_COMPLETED) {
            $data['completed_at']  = now();
            $data['error_message'] = null;
        }

        $download->update($data);

        return $download->refresh();
    }

    public function markPending(VdDownload $download): VdDownload
    {
        return $this->transition($download, VdDownload::STATUS_PENDING);
    }

    public function markProcessing(VdDownload $download): VdDownload
    {
        return $this->transition($download, VdDownload::STATUS_PROCESSING);
    }

    public function markCompleted(
        VdDownload $download,
        string     $filePath,
        string     $fileName,
        int        $fileSize
    ): VdDownload {
        return $this->transition($download, VdDownload::STATUS_COMPLETED, [
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
        ]);
    }

    public function markFailed(VdDownload $download, string $errorMessage): VdDownload
    {
        return $this->transition($download, VdDownload::STATUS_FAILED, [
            'error_message' => $errorMessage,
        ]);
    }

    public function markCancelled(VdDownload $download): VdDownload
    {
        return $this->transition($download, VdDownload::STATUS_CANCELLED);
    }
}