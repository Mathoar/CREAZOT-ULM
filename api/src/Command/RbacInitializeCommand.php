<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\RbacInitializer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:rbac:init', description: 'Initialise les rôles et permissions par défaut')]
class RbacInitializeCommand extends Command
{
    public function __construct(
        private readonly RbacInitializer $initializer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->initializer->initialize();

        $io->success('Rôles et permissions initialisés avec succès.');

        return Command::SUCCESS;
    }
}
