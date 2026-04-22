<?php

namespace App\Service;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class DynamicMailerFactory
{
    public function __construct(
        private ClientGetter $clientGetter,
        private Environment $twig,
    ) {}

    public function getMailerForClient(): MailerInterface
    {
        $client = $this->clientGetter->get();
        $dsn = $client->getEmailServer();

        if (!$dsn) {
            throw new \RuntimeException("Le client n'a pas de DSN email configuré.");
        }

        $transport = Transport::fromDsn($dsn);

        return new Mailer($transport);
    }

    /**
     * Rend un TemplatedEmail en Email standard (body HTML résolu via Twig)
     * puis l'envoie via le mailer dynamique du client.
     * Nécessaire car le Mailer dynamique (hors conteneur) ne possède pas
     * le BodyRenderer qui transformerait automatiquement le template.
     */
    public function renderAndSend(MailerInterface $mailer, TemplatedEmail $templatedEmail): void
    {
        $html = $this->twig->render(
            $templatedEmail->getHtmlTemplate(),
            $templatedEmail->getContext(),
        );

        $email = (new Email())
            ->from($templatedEmail->getFrom()[0])
            ->subject($templatedEmail->getSubject())
            ->html($html);

        foreach ($templatedEmail->getTo() as $to) {
            $email->addTo($to);
        }

        $mailer->send($email);
    }
}
