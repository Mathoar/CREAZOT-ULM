<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Reservation;
use App\Service\ShortcodeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Génère le publicShortcode pour les réservations futures (date >= now)
 * dont le client a hasPlanification = true.
 *
 * Usage : bin/console app:reservations:backfill-shortcodes [--dry-run]
 */
#[AsCommand(
    name: 'app:reservations:backfill-shortcodes',
    description: 'Génère les shortcodes publics pour les réservations futures sans shortcode'
)]
final class BackfillReservationShortcodesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShortcodeGenerator $generator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Affiche ce qui serait fait sans rien modifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        $now = new \DateTime();

        $qb = $this->em->createQueryBuilder()
            ->select('r')
            ->from(Reservation::class, 'r')
            ->where('r.publicShortcode IS NULL')
            ->andWhere('r.debut >= :now')
            ->setParameter('now', $now);

        $reservations = $qb->getQuery()->getResult();
        $total = count($reservations);

        if ($total === 0) {
            $io->success('Aucune réservation à traiter.');
            return Command::SUCCESS;
        }

        $io->note(sprintf('%d réservation(s) future(s) à traiter%s', $total, $dryRun ? ' (dry-run)' : ''));

        $io->progressStart($total);
        $processed = 0;

        foreach ($reservations as $reservation) {
            /** @var Reservation $reservation */
            $code = $this->generator->generate();
            $reservation->setPublicShortcode($code);

            $processed++;
            $io->progressAdvance();

            if (!$dryRun && $processed % 50 === 0) {
                $this->em->flush();
            }
        }

        if (!$dryRun) {
            $this->em->flush();
        }

        $io->progressFinish();
        $io->success(sprintf(
            '%s : %d réservation(s) %s',
            $dryRun ? 'Simulation' : 'Backfill terminé',
            $processed,
            $dryRun ? 'auraient reçu un shortcode' : 'ont reçu un shortcode'
        ));

        return Command::SUCCESS;
    }
}
