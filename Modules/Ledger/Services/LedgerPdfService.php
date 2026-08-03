<?php

namespace Modules\Ledger\Services;

use App\Models\Company;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class LedgerPdfService
{
    public function download(string $view, array $data, string $filename): \Symfony\Component\HttpFoundation\Response
    {
        $html = view($view, $data)->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 15,
            'margin_left' => 12,
            'margin_right' => 12,
            'fontDir' => array_merge($fontDirs, config('mpdf.fontDir', [])),
            'fontdata' => array_merge($fontData, config('mpdf.fontdata', [])),
            'default_font' => $this->fontForLocale(),
            'useSubstitutions' => false,
            'shrink_tables_to_fit' => 1,
        ]);

         
        // Enable complex-script shaping (required for connected Urdu Nastaliq / Arabic ligatures)
        if (in_array(app()->getLocale(), ['ur', 'ar'])) {
            $mpdf->useAdobeCJK = false;
            $mpdf->autoLangToFont = true;
            $mpdf->autoScriptToLang = true;
            // $mpdf->SetDirectionality('rtl');
        }
        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename, Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    protected function fontForLocale(): string
    {
        return match (app()->getLocale()) {
            'ur' => 'jameelnoorinastaleeq',
            'ar' => 'notonaskharabic',
            default => 'dejavusans',
        };
    }

    public function companyLetterhead(Company $company): array
    {
        return [
            'name' => $company->name,
            'address' => $company->address,
            'phone' => $company->phone,
            'email' => $company->email,
            'logo' => $company->logo ? storage_path('app/public/' . $company->logo) : null,
        ];
    }
}