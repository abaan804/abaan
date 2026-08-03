<?php

namespace Modules\VideoDownloader\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\VideoDownloader\Models\VdActivityLog;
use Modules\VideoDownloader\Models\VdDownload;
use Modules\VideoDownloader\Requests\UpdateSettingRequest;
use Modules\VideoDownloader\Services\DownloadSettingService;
use Modules\VideoDownloader\Services\DownloadStorageService;
use Modules\VideoDownloader\Repositories\DownloadRepository;

class SettingController extends Controller
{
    public function __construct(
        protected DownloadSettingService $settingService,
        protected DownloadStorageService $storageService,
        protected DownloadRepository     $downloadRepo,
    ) {
    }

    public function index(\Illuminate\Http\Request $request): View
    {
        abort_unless($request->user()->can('videodownloader.manage-settings'), 403);

        $companyId   = $request->user()->company_id;
        $setting     = $this->settingService->forCompany($companyId);
        $storageUsed = $this->downloadRepo->totalStorageUsed($companyId);
        $platforms   = array_keys(config('videodownloader.platforms', []));

        return view('videodownloader::settings.index', compact(
            'setting', 'storageUsed', 'platforms'
        ));
    }

    public function update(UpdateSettingRequest $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $data = $request->safe()->toArray();

        // Normalize checkboxes that may not be submitted when unchecked
        $data['allow_audio_only']   = $request->boolean('allow_audio_only');
        $data['notify_on_complete'] = $request->boolean('notify_on_complete');
        $data['notify_on_failure']  = $request->boolean('notify_on_failure');

        // Empty allowed_platforms array = allow all
        if (empty($data['allowed_platforms'])) {
            $data['allowed_platforms'] = null;
        }

        $setting = $this->settingService->update($companyId, $data);

        // Create a temporary VdDownload instance for logging without a real download
        VdActivityLog::create([
            'company_id'  => $companyId,
            'user_id'     => $request->user()->id,
            'download_id' => null,
            'action'      => VdActivityLog::ACTION_SETTINGS_UPDATED,
            'properties'  => [
                'max_file_size_mb'         => $setting->max_file_size_mb,
                'retention_days'           => $setting->retention_days,
                'max_concurrent_downloads' => $setting->max_concurrent_downloads,
            ],
            'created_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Settings updated successfully.'),
        ]);
    }
}