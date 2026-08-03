<?php

namespace Modules\FamilyTree\Services;

use Modules\FamilyTree\Models\FtFamily;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

class FamilyTreePdfService
{
    public function download(string $view, array $data, string $filename): Response
    {
        $html = view($view, $data)->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $defaultFontConfig = (new FontVariables())->getDefaults();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 12,
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
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function familyLetterhead(FtFamily $family): array
    {
        return [
            'family_name' => $family->name,
            'village' => $family->village,
            'city' => $family->city,
            'country' => $family->country,
            'photo' => $family->photo ? storage_path('app/public/' . $family->photo) : null,
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