<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\SecurityEvent;
use App\Repository\SiteSettingsRepository;
use App\Service\ClientGetter;
use App\Service\DynamicMailerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SecurityEventSubscriber implements EventSubscriberInterface
{
    public const TYPE_LABELS = [
        'incident' => 'Incident',
        'accident' => 'Accident',
        'quasi_accident' => 'Quasi-accident',
        'observation' => 'Observation',
        'note_interne' => 'Note interne',
    ];

    public function __construct(
        private readonly DynamicMailerFactory $dynamicMailerFactory,
        private readonly ClientGetter $clientGetter,
        private readonly SiteSettingsRepository $siteSettingsRepository,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['prefillReport', EventPriorities::PRE_WRITE],
                ['notifyExploitant', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function prefillReport(ViewEvent $event): void
    {
        $securityEvent = $event->getControllerResult();
        if (!$securityEvent instanceof SecurityEvent || $event->getRequest()->getMethod() !== Request::METHOD_POST) {
            return;
        }

        if ($securityEvent->getCompteRenduSuivi()) {
            return;
        }

        $description = $securityEvent->getDescription();

        $html = <<<HTML
<h3>1. Description des faits</h3>
<p>{$description}</p>

<h3>2. Circonstances</h3>
<p><em>Conditions météo, phase de vol, environnement…</em></p>

<h3>3. Conséquences</h3>
<p><em>Dommages matériels, corporels, impact sur l'exploitation…</em></p>

<h3>4. Analyse des causes</h3>
<p><em>Facteurs contributifs identifiés…</em></p>

<h3>5. Mesures correctives</h3>
<ul>
<li><em>Action corrective 1…</em></li>
<li><em>Action corrective 2…</em></li>
</ul>

<h3>6. Retour d'expérience</h3>
<p><em>Enseignements tirés et communication aux pilotes…</em></p>
HTML;

        $securityEvent->setCompteRenduSuivi($html);
    }

    public function notifyExploitant(ViewEvent $event): void
    {
        $securityEvent = $event->getControllerResult();
        if (!$securityEvent instanceof SecurityEvent || $event->getRequest()->getMethod() !== Request::METHOD_POST) {
            return;
        }

        $client = $this->clientGetter->get();
        if (!$client?->getEmailServer() || !$client->getEmailAddressSender()) {
            return;
        }

        $type = $securityEvent->getType();
        $typeLabel = self::TYPE_LABELS[$type] ?? $type;
        $isNoteInterne = ($type === 'note_interne');

        $pilote = $securityEvent->getPilote();
        $aeronef = $securityEvent->getAeronef();

        $context = [
            'typeLabel' => $typeLabel,
            'description' => $securityEvent->getDescription(),
            'dateEvenement' => $securityEvent->getDateEvenement()?->format('d/m/Y H:i'),
            'piloteNom' => $pilote ? trim($pilote->getFirstName() . ' ' . $pilote->getLastName()) : '—',
            'aeronefImmat' => $aeronef?->getImmatriculation() ?? '—',
            'isNoteInterne' => $isNoteInterne,
            'client' => $client,
        ];

        if (!$isNoteInterne) {
            $settings = $this->siteSettingsRepository->findInstance();
            $context['delaiDGAC'] = $settings?->getDelaiNotificationDGACHeures() ?? 72;
            $context['delaiCR'] = $settings?->getDelaiCompteRenduSuiviJours() ?? 30;
        }

        $subject = $isNoteInterne
            ? "Note interne enregistrée"
            : "Événement sécurité — {$typeLabel}";

        try {
            $mailer = $this->dynamicMailerFactory->getMailerForClient();

            $message = (new TemplatedEmail())
                ->from($client->getEmailAddressSender())
                ->to($client->getEmail())
                ->subject($subject)
                ->htmlTemplate('emails/security_event.html.twig')
                ->context($context);

            $this->dynamicMailerFactory->renderAndSend($mailer, $message);

            $securityEvent->setDateNotificationExploitant(new \DateTime());
            $this->em->flush();

            $this->logger->info('Email événement sécurité envoyé pour {type}', [
                'type' => $typeLabel,
                'to' => $client->getEmail(),
                'clientId' => $client->getId(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Échec envoi email événement sécurité: {error}', [
                'error' => $e->getMessage(),
                'clientId' => $client->getId(),
            ]);
        }
    }
}
