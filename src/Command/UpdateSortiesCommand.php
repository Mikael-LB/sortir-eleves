<?php

namespace App\Command;

use App\Utils\ArchivageSorties;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateSortiesCommand extends Command
{
    protected static $defaultName = 'UpdateSortiesCommand';
    protected static $defaultDescription = 'Change l\'Etat des sortie en fonction de la date du jour';
    private $archivageSorties;

    public function __construct(string $name = null, ArchivageSorties $archivageSorties)
    {
        parent::__construct($name);
        $this->archivageSorties = $archivageSorties;
    }


    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->success('La commande d\'actualisation de la Base De Donnée se lance bien');

        $io->writeln('Démarrage de l\'actualisation');
        $io->writeln('Préparez-vous à attendre un moment...');
        $this->archivageSorties->archiver();
        $io->writeln('Fin de l\'actualisation');


        $io->success('La commande d\'actualisation s\'est effectuée sans erreur');

        return Command::SUCCESS;
    }
}
