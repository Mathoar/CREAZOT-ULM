<?php

declare(strict_types=1);

namespace App\Service\Manex;

use App\Entity\Client;
use App\Entity\ManexSection;
use Twig\Environment;

final class ManexRenderer
{
    public function __construct(
        private readonly Environment $twig,
    ) {}

    /**
     * @param ManexSection[] $sections
     */
    public function renderHtml(Client $client, array $sections, array $collectedData, ?string $versionNumber = null): string
    {
        $renderedSections = [];

        foreach ($sections as $section) {
            if (!$section->getIsEnabled()) {
                continue;
            }

            $key = $section->getSectionKey();
            $autoHtml = '';

            if ($section->getHasAutoContent()) {
                $templateName = "manex/sections/{$key}.html.twig";
                $autoHtml = $this->twig->render($templateName, [
                    'data'    => $collectedData,
                    'client'  => $client,
                    'section' => $section,
                ]);
            }

            $renderedSections[] = [
                'key'       => $key,
                'title'     => $section->getTitle(),
                'position'  => $section->getPosition(),
                'introHtml' => $section->getIntroHtml(),
                'autoHtml'  => $autoHtml,
                'customHtml' => $section->getCustomHtml(),
            ];
        }

        usort($renderedSections, fn($a, $b) => $a['position'] <=> $b['position']);

        return $this->twig->render('manex/base.html.twig', [
            'client'          => $client,
            'sections'        => $renderedSections,
            'versionNumber'   => $versionNumber,
            'generationDate'  => new \DateTimeImmutable(),
            'encodedLogo'     => $collectedData['encodedLogo'] ?? null,
        ]);
    }
}
