<?php

namespace Modules\VideoDownloader\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\VideoDownloader\Repositories\DownloadRepository;
use Modules\VideoDownloader\Services\DownloadStorageService;
use Modules\VideoDownloader\Services\DownloadSettingService;

class DashboardController extends Controller
{
    public function __construct(
        protected DownloadRepository   $downloadRepo,
        protected DownloadStorageService $storageService,
        protected DownloadSettingService $settingService
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('videodownloader.view-dashboard'), 403);

        $companyId = $request->user()->company_id;
        $stats     = $this->downloadRepo->stats($companyId);
        $recent    = $this->downloadRepo->recentForDashboard($companyId, 8);
        $platforms = $this->downloadRepo->platformBreakdown($companyId);
        $formats   = $this->downloadRepo->formatBreakdown($companyId);
        $setting   = $this->settingService->forCompany($companyId);

        // Storage usage as percentage of limit
        $storageUsed    = $stats['storage_used'];
        $storageLimitBytes = $setting->storage_limit_bytes;
        $storagePercent = $storageLimitBytes
            ? min(100, round(($storageUsed / $storageLimitBytes) * 100, 1))
            : null;

        return view('videodownloader::dashboard.index', compact(
            'stats', 'recent', 'platforms', 'formats',
            'setting', 'storageUsed', 'storageLimitBytes', 'storagePercent'
        ));
    }
}