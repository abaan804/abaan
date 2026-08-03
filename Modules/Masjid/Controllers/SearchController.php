<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Masjid\Models\MasjidMember;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidPayment;
use Modules\Masjid\Models\MasjidSeason;

class SearchController extends Controller
{
    public function search(Request $request, MasjidMosque $mosque): JsonResponse
    {
        $query = trim((string) $request->get('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }
       
        $results = [];

        if ($request->user()->can('masjid.manage-members')) {
             
            $members = MasjidMember::where('mosque_id', $mosque->id)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                    ->orWhere('mobile', 'like', "%{$query}%")
                    ->orWhere('cnic', 'like', "%{$query}%");
                })
                ->get();
            foreach ($members as $m) {
                $results[] = [
                    'group' => __('Members'),
                    'icon' => 'bi-person',
                    'title' => $m->name,
                    'subtitle' => $m->mobile ?? '',
                    'url' => route('masjid.mosque.members.statement', [$mosque, $m]),
                ];
            }
          
        }
 
        if ($request->user()->can('masjid.manage-seasons')) {
           $seasons = MasjidSeason::where('mosque_id', $mosque->id)
                    ->where('name', 'like', "%{$query}%")
                    ->take(3)
                    ->get();
            foreach ($seasons as $s) {
                $results[] = [
                    'group' => __('Seasons'),
                    'icon' => 'bi-calendar3',
                    'title' => $s->name,
                    'subtitle' => $s->start_date->format('M Y') . ' — ' . $s->end_date->format('M Y'),
                    'url' => route('masjid.mosque.seasons.members', [$mosque, $s]),
                ];
            }
        }

        if ($request->user()->can('masjid.manage-payments')) {
           $payments = MasjidPayment::where('mosque_id', $mosque->id)
                    ->where(function ($q) use ($query) {
                        $q->where('receipt_no', 'like', "%{$query}%")
                        ->orWhere('reference_no', 'like', "%{$query}%");
                    })
                    ->with('member')
                    ->take(3)
                    ->get();

            foreach ($payments as $p) {
                $results[] = [
                    'group' => __('Payments'),
                    'icon' => 'bi-cash',
                    'title' => $p->receipt_no ?? $p->reference_no,
                    'subtitle' => $p->member?->name ?? '',
                    'url' => route('masjid.mosque.payments.index', $mosque),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}