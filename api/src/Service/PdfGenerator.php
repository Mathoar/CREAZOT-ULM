<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use App\Entity\Cadeau;
use App\Entity\Client;
use App\Service\ClientGetter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PdfGenerator
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ClientGetter $clientGetter,
        #[Autowire('%image.public_dir%')] private readonly string $publicDir,
    ) {}

    public function generate(Cadeau $data): string
    {
        $client = $data->getClient() ?? $this->clientGetter->get();
        $dompdf = new Dompdf();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);

        $html = $this->twig->render('bon_cadeau/pdf.html.twig', [
            'cadeau' => $data,
            'encoded_image' => $this->getEncodedImage($client),
            'client' => $client
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function getEncodedImage(?Client $client = null): ?string
    {
        $imagePath = null;

        if ($client && $client->getPdfBackground()) {
            $candidatePath = rtrim($this->publicDir, '/') . $client->getPdfBackground();
            if (file_exists($candidatePath)) {
                $imagePath = $candidatePath;
            }
        }

        if (!$imagePath) {
            $fallback = rtrim($this->publicDir, '/') . '/images/Plane.png';
            if (file_exists($fallback)) {
                $imagePath = $fallback;
            }
        }

        if ($imagePath && file_exists($imagePath)) {
            $mime = str_ends_with(strtolower($imagePath), '.png') ? 'image/png' : 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($imagePath));
        }

        return null;
    }
}
