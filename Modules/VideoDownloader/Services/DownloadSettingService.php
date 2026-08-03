<?php

namespace Modules\VideoDownloader\Services;

use Modules\VideoDownloader\Models\VdSetting;

class DownloadSettingService
{
    /**
     * Get settings for a company — creates defaults if none exist.
     */
    public function forCompany(int $companyId): VdSetting
    {
        return VdSetting::firstOrCreate(
            ['company_id' => $companyId],
            [
                'max_file_size_mb'         => config('videodownloader.defaults.max_file_size_mb', 500),
                'max_concurrent_downloads' => config('videodownloader.defaults.max_concurrent_downloads', 3),
                'retention_days'           => config('videodownloader.defaults.retention_days', 30),
                'allowed_platforms'        => null,
                'allow_audio_only'         => config('videodownloader.defaults.allow_audio_only', true),
                'storage_limit_gb'         => null,
                'notify_on_complete'       => true,
                'notify_on_failure'        => true,
            ]
        );
    }

    /**
     * Update settings for a company.
     */
    public function update(int $companyId, array $data): VdSetting
    {
        $setting = $this->forCompany($companyId);
        $setting->update(array_merge($data, ['updated_by' => auth()->id()]));
        return $setting->refresh();
    }
}