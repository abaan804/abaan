<?php

namespace Modules\VideoDownloader\Policies;

use App\Models\User;
use Modules\VideoDownloader\Models\VdDownload;

class VdDownloadPolicy
{
    /**
     * View a specific download — must belong to same company.
     * Staff may only see their own downloads.
     */
    public function view(User $user, VdDownload $download): bool
    {
        if ($download->company_id !== $user->company_id) return false;

        if ($user->hasRole('company-owner')) return true;

        return $download->user_id === $user->id;
    }

    /**
     * Serve (download) a completed file.
     */
    public function serve(User $user, VdDownload $download): bool
    {
        return $this->view($user, $download)
            && $download->status === VdDownload::STATUS_COMPLETED
            && ! empty($download->file_path);
    }

    /**
     * Cancel a pending or processing download.
     */
    public function cancel(User $user, VdDownload $download): bool
    {
        return $this->view($user, $download)
            && in_array($download->status, [
                VdDownload::STATUS_PENDING,
                VdDownload::STATUS_PROCESSING,
            ]);
    }

    /**
     * Retry a failed download.
     */
    public function retry(User $user, VdDownload $download): bool
    {
        return $this->view($user, $download)
            && $download->is_retryable;
    }

    /**
     * Delete a download record (soft delete).
     */
    public function delete(User $user, VdDownload $download): bool
    {
        return $this->view($user, $download)
            && in_array($download->status, [
                VdDownload::STATUS_COMPLETED,
                VdDownload::STATUS_FAILED,
                VdDownload::STATUS_CANCELLED,
            ]);
    }
}