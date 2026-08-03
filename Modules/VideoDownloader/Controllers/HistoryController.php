<?php

namespace Modules\VideoDownloader\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\VideoDownloader\Repositories\DownloadRepository;

class HistoryController extends Controller
{
    public function __construct(protected DownloadRepository $downloadRepo)
    {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('videodownloader.view-history'), 403);

        return view('videodownloader::history.index');
    }

    public function table(Request $request): View
    {
        abort_unless($request->user()->can('videodownloader.view-history'), 403);

        $companyId = $request->user()->company_id;

        // Staff see only their own — owners see all
        $filters = $request->only([
            'search', 'status', 'platform',
            'format_ext', 'date_from', 'date_to',
            'sort', 'dir',
        ]);

        if (! $request->user()->hasRole('company-owner')) {
            $filters['user_id'] = $request->user()->id;
        }

        $downloads = $this->downloadRepo->paginate($companyId, $filters, 15);

        return view('videodownloader::history._table', compact('downloads'));
    }
}