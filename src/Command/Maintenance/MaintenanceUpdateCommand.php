<?php

namespace App\Command\Maintenance;

use App\Entity\Manual;
use App\Service\ManualService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:update:binaries',
    description: 'Re-Download binaries from LEGO.com',
)]
class MaintenanceUpdateCommand extends Command
{
    public function __construct(
        protected readonly ManualService $manualService,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ParameterBagInterface $parameterBag,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Download missing binaries from LEGO.com');

        $repository = $this->entityManager->getRepository(Manual::class);
        $dataDir = $this->parameterBag->get('app.data_dir');
        $io->writeln('Data directory: ' . $dataDir);
        $io->newLine();

        $manuals = $repository->findAll();
        foreach ($manuals as $manual) {
            $io->writeln($manual->getSet()->getNumber() . ' / ' . $manual->getSet()->getName() . ' / ' . $manual->getId());
            try {
                $this->manualService->fetchFiles($manual, $io);
            } catch (Exception $e) {
                $io->error($e->getMessage());
            }
            $io->newLine();
        }

        $io->success('Finished');

        return Command::SUCCESS;
    }
}
