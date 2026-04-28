<?php

declare(strict_types=1);

namespace App\Service\Manex;

use Dompdf\Dompdf;
use Dompdf\Options;

final class ManexPdfGenerator
{
    public function generate(string $html): string
    {
        $dompdf = new Dompdf();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
