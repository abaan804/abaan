<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSeason;
use Modules\Masjid\Models\MasjidSeasonMember;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

class SeasonCollectionListController extends Controller
{
    public function download(
        Request       $request,
        MasjidMosque  $mosque,
        MasjidSeason  $season
    ): Response {
        abort_unless(
            $mosque->company_id === $request->user()->company_id
            && $season->mosque_id === $mosque->id,
            403
        );

        // Load season members with member details
        $seasonMembers = MasjidSeasonMember::where('season_id', $season->id)
            ->where('mosque_id', $mosque->id)
            ->with('member')
            ->orderBy('id')
            ->get();

        $totalMembers  = $seasonMembers->count();
        $amountPerMember = $season->contribution_amount ?? 0;
        $totalAmount   = $amountPerMember * $totalMembers;

        // Detect locale for RTL
        $isUrdu = app()->getLocale() === 'ur';

        $html = view('masjid::seasons.blank_collection_list', compact(
            'mosque',
            'season',
            'seasonMembers',
            'totalMembers',
            'amountPerMember',
            'totalAmount',
            'isUrdu'
        ))->render();

        $filename = 'collection-list-'
            . \Illuminate\Support\Str::slug($mosque->mosque_name)
            . '-'
            . \Illuminate\Support\Str::slug($season->name)
            . '.pdf';

        return $this->makePdf($html, $filename, $isUrdu);
    }

    protected function makePdf(string $html, string $filename, bool $isUrdu): Response
    {
        $defaultConfig     = (new ConfigVariables())->getDefaults();
        $defaultFontConfig = (new FontVariables())->getDefaults();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 10,
            'margin_bottom' => 14,
            'margin_left'   => $isUrdu ? 10 : 14,
            'margin_right'  => $isUrdu ? 14 : 10,
            'fontDir'       => array_merge(
                $defaultConfig['fontDir'],
                config('mpdf.fontDir', [])
            ),
            'fontdata'      => array_merge(
                $defaultFontConfig['fontdata'],
                config('mpdf.fontdata', [])
            ),
            'default_font'  => $isUrdu ? 'JameelNooriNastaleeq' : 'dejavusans',
            'direction'     => $isUrdu ? 'rtl' : 'ltr',
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont   = true;

        // Repeat table header on every page
        $mpdf->setAutoTopMargin    = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';

        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output($filename, Destination::STRING_RETURN),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }
}