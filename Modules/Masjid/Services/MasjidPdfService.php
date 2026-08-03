<?php

namespace Modules\Masjid\Services;

use Modules\Masjid\Models\MasjidMosque;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

class MasjidPdfService
{
    public function download(string $view, array $data, string $filename): Response
    {
        $html = view($view, $data)->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $defaultFontConfig = (new FontVariables())->getDefaults();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 15,
            'margin_left' => 12,
            'margin_right' => 12,
            'fontDir' => array_merge($defaultConfig['fontDir'], config('mpdf.fontDir', [])),
            'fontdata' => array_merge($defaultFontConfig['fontdata'], config('mpdf.fontdata', [])),
            'default_font' => $this->fontForLocale(),
        ]);

        if (in_array(app()->getLocale(), ['ur', 'ar'])) {
            $mpdf->SetDirectionality('rtl');
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
        }

        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename, Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function mosqueLetterhead(MasjidMosque $mosque): array
    {
        return [
            'mosque_name' => $mosque->mosque_name,
            'village_name' => $mosque->village_name,
            'scholar_name' => $mosque->scholar_name,
            'committee_name' => $mosque->committee_name,
            'mosque_contact' => $mosque->mosque_contact,
            'address' => $mosque->address,
            'city' => $mosque->city,
            'logo' => $mosque->logo ? storage_path('app/public/' . $mosque->logo) : null,
        ];
    }

    protected function fontForLocale(): string
    {
        return match (app()->getLocale()) {
            'ur' => 'jameelnoorinastaleeq',
            'ar' => 'notonaskharabic',
            default => 'dejavusans',
        };
    }
}