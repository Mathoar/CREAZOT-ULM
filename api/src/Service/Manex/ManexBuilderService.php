<?php

declare(strict_types=1);

namespace App\Service\Manex;

use App\Entity\Client;
use App\Entity\ManexSection;
use App\Entity\ManexVersion;
use App\Entity\MediaObject;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ManexBuilderService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ManexDataCollector $dataCollector,
        private readonly ManexRenderer $renderer,
        private readonly ManexPdfGenerator $pdfGenerator,
        #[Autowire('%kernel.project_dir%/public/media')] private readonly string $uploadDir,
    ) {}

    public function ensureSections(Client $client): void
    {
        $existing = $this->em->getRepository(ManexSection::class)
            ->findBy(['client' => $client]);

        $existingKeys = array_map(fn(ManexSection $s) => $s->getSectionKey(), $existing);

        foreach (ManexSections::SECTIONS as $key => $meta) {
            if (in_array($key, $existingKeys, true)) {
                continue;
            }

            $section = new ManexSection();
            $section->setClient($client);
            $section->setSectionKey($key);
            $section->setTitle($meta['title']);
            $section->setPosition($meta['position']);
            $section->setHasAutoContent($meta['auto']);

            $defaultContent = ManexSections::getDefaultContent($key);
            if ($key === 'procedures_pax' && $client->getHasPassengerRegistration()) {
                $defaultContent = $this->buildPassengerRegistrationContent($client);
            }
            if ($key === 'analyse_risques') {
                $defaultContent = $this->buildAnalyseRisquesContent($client);
            }
            if ($defaultContent) {
                $section->setCustomHtml($defaultContent);
            }

            $this->em->persist($section);
        }

        $this->em->flush();
    }

    public function preview(Client $client): string
    {
        $sections = $this->getSections($client);
        $data = $this->dataCollector->collect($client);
        return $this->renderer->renderHtml($client, $sections, $data);
    }

    public function generate(Client $client, User $user, ?string $changelog = null): ManexVersion
    {
        $sections = $this->getSections($client);
        $data = $this->dataCollector->collect($client);

        $versionNumber = $this->nextVersionNumber($client);
        $html = $this->renderer->renderHtml($client, $sections, $data, $versionNumber);
        $pdfContent = $this->pdfGenerator->generate($html);

        $mediaObject = new MediaObject();
        $mediaObject->setClient($client);
        $filename = sprintf('MANEX_%s_v%s_%s.pdf', $client->getSlug(), $versionNumber, date('Ymd_His'));
        $mediaObject->filePath = $filename;
        $mediaObject->description = "MANEX v{$versionNumber}";
        $mediaObject->createdAt = new \DateTimeImmutable();

        $uploadDir = $this->uploadDir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        file_put_contents($uploadDir . '/' . $filename, $pdfContent);

        $this->em->persist($mediaObject);

        $version = new ManexVersion();
        $version->setClient($client);
        $version->setVersionNumber($versionNumber);
        $version->setGeneratedBy($user);
        $version->setDocument($mediaObject);
        $version->setChangelog($changelog);
        $version->setSectionSnapshot($this->buildSnapshot($sections));
        $this->em->persist($version);

        $this->em->flush();

        return $version;
    }

    /**
     * @return ManexSection[]
     */
    private function getSections(Client $client): array
    {
        $this->ensureSections($client);

        return $this->em->getRepository(ManexSection::class)
            ->findBy(['client' => $client], ['position' => 'ASC']);
    }

    private function nextVersionNumber(Client $client): string
    {
        $last = $this->em->getRepository(ManexVersion::class)
            ->findOneBy(['client' => $client], ['generatedAt' => 'DESC']);

        if (!$last) {
            return '1.0';
        }

        $parts = explode('.', $last->getVersionNumber());
        $major = (int) ($parts[0] ?? 1);
        $minor = (int) ($parts[1] ?? 0);

        return $major . '.' . ($minor + 1);
    }

    /**
     * @param ManexSection[] $sections
     */
    private function buildPassengerRegistrationContent(Client $client): string
    {
        $hasWeight = $client->getHasWeightCollection();
        $infoList = 'nom, prénom, coordonnées';
        if ($hasWeight) {
            $infoList = 'nom, prénom, poids, coordonnées';
        }

        $html = '<h3>Enregistrement des passagers</h3>
<p>L\'exploitant utilise un système d\'enregistrement en ligne des passagers via la plateforme Logic\'Ciel. Avant chaque vol, les passagers sont invités à s\'enregistrer en fournissant leurs informations personnelles (' . $infoList . ').</p>
<h4>Consentement et acceptation des conditions</h4>
<p>Lors de l\'enregistrement, chaque passager doit obligatoirement prendre connaissance et accepter les conditions suivantes avant de pouvoir finaliser son inscription :</p>';

        $consentText = $client->getConsentText();
        if ($consentText) {
            $html .= '<blockquote style="border-left: 3px solid #4a5568; padding: 8px 12px; margin: 10px 0; background: #f7fafc; font-style: italic;">'
                . $consentText
                . '</blockquote>';
        }

        if ($hasWeight) {
            $html .= '<h4>Collecte du poids passager</h4>
<p>Le poids de chaque passager est collecté lors de l\'enregistrement en ligne. Cette information permet au pilote commandant de bord de vérifier que la masse totale au décollage (pilote + passager + carburant) reste dans les limites définies par le constructeur et les règles de vol de l\'exploitant.</p>
<p>En cas de dépassement du poids maximal passager défini dans les règles de vol, le pilote en est informé et prend la décision appropriée (adaptation de la charge carburant, refus d\'embarquement, ou changement d\'aéronef si disponible).</p>';
        }

        $html .= '<p>Ce dispositif permet de garantir la traçabilité des passagers, le respect de la réglementation en matière de consentement éclairé';
        if ($hasWeight) {
            $html .= ', et la bonne gestion des masses et du centrage de l\'aéronef';
        }
        $html .= '.</p>';

        return $html;
    }

    private function buildAnalyseRisquesContent(Client $client): string
    {
        $data = $this->dataCollector->collect($client);
        $apCircuits = array_filter($data['circuits'], fn($c) => $c['isParticularActivity'] ?? false);

        $html = ManexSections::getDefaultContent('analyse_risques') ?? '';

        if (empty($apCircuits)) {
            $html .= '<p><em>Aucun circuit associé à une activité particulière (AP) n\'a été identifié. '
                . 'Les grilles d\'analyse seront à compléter lorsque des circuits avec une nature AP seront configurés.</em></p>';
            return $html;
        }

        $html .= '<h3>Grilles d\'analyse des risques par activité particulière</h3>';

        foreach ($apCircuits as $circuit) {
            $title = ($circuit['nature'] ?? 'AP') . ' — ' . $circuit['nom'];
            if (!empty($circuit['code'])) {
                $title .= ' (' . $circuit['code'] . ')';
            }
            $html .= '<h4>' . htmlspecialchars($title) . '</h4>';

            if (!empty($circuit['qualifications'])) {
                $html .= '<p><strong>Qualifications requises :</strong> '
                    . htmlspecialchars(implode(', ', $circuit['qualifications'])) . '</p>';
            }

            $html .= '<table>
    <thead>
        <tr>
            <th>Menace identifiée</th>
            <th>Probabilité</th>
            <th>Gravité</th>
            <th>Niveau de risque</th>
            <th>Mesures d\'atténuation</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><em>Conditions météo défavorables</em></td>
            <td><em>Probable</em></td>
            <td><em>Majeur</em></td>
            <td><em>Élevé</em></td>
            <td><em>Consultation METAR/TAF, minima définis, décision GO/NO GO</em></td>
        </tr>
        <tr>
            <td><em>Panne moteur en vol</em></td>
            <td><em>Rare</em></td>
            <td><em>Critique</em></td>
            <td><em>Élevé</em></td>
            <td><em>Maintien en vol plané, zones d\'atterrissage identifiées, suivi maintenance</em></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>';
        }

        $html .= '<p><em>Les lignes en italique sont des exemples indicatifs. L\'exploitant doit compléter et adapter chaque grille '
            . 'en fonction des risques spécifiques à son activité et à ses conditions locales d\'exploitation.</em></p>';

        return $html;
    }

    private function buildSnapshot(array $sections): array
    {
        $snapshot = [];
        foreach ($sections as $section) {
            $snapshot[$section->getSectionKey()] = [
                'title'   => $section->getTitle(),
                'enabled' => $section->getIsEnabled(),
            ];
        }
        return $snapshot;
    }
}
