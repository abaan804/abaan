<?php

namespace Modules\VideoDownloader\Services;

use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

class VideoDownloadPdfService
{
    public function download(string $view, array $data, string $filename): Response
    {
        $html = view($view, $data)->render();

        $defaultConfig     = (new ConfigVariables())->getDefaults();
        $defaultFontConfig = (new FontVariables())->getDefaults();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 12,
            'margin_bottom' => 15,
            'margin_left'   => 12,
            'margin_right'  => 12,
            'fontDir'       => array_merge($defaultConfig['fontDir'], config('mpdf.fontDir', [])),
            'fontdata'      => array_merge($defaultFontConfig['fontdata'], config('mpdf.fontdata', [])),
            'default_font'  => 'dejavusans',
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename, Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}