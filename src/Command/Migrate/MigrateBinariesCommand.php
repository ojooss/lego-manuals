<?php

namespace App\Command\Migrate;

use App\Entity\Manual;
use App\Service\DownloadService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate:binaries',
    description: 'Migrate binaries from filesystem to database',
)]
class MigrateBinariesCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly DownloadService $downloadService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Run test')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Migrate from filesystem to database');

        $repository = $this->entityManager->getRepository(Manual::class);
        #$dataDir = __DIR__ . '/../../../public/data/';
        $dataDir = '/var/www/html/data/';

        if ($input->getOption('test')) {
            $io->info('Preparing for test');
            /** @var Manual $manual */
            $manuals = $repository->findBy([], [], 1);
            if (empty($manuals)) {
                $io->error('Could not load test data');
            }
            $manual = current($manuals);
            $newManual = new Manual();
            $newManual->setSet($manual->getSet());
            $newManual->setUrl($manual->getUrl());
            $newManual->setFilename($manual->getFilename());
            $newManual->setCovername($manual->getCovername());
            file_put_contents(
                $dataDir . $newManual->getFilename(),
                stream_get_contents($manual->getFile())
            );
            file_put_contents(
                $dataDir . $newManual->getCovername(),
                stream_get_contents($manual->getCover())
            );
            $this->entityManager->persist($newManual);
            $this->entityManager->flush();
        }

        $io->info('Start migration');

        $manuals = $repository->findBy(['file' => null]);
        if (empty($manuals)) {
            $io->writeln('There is nothing left to be migrated');
        }
        foreach ($manuals as $manual) {
            $io->writeln($manual->getSet() . ' / ' . $manual->getFilename());
            try {
                $pathToPdf = $dataDir . $manual->getFilename();
                if (!file_exists($pathToPdf)) {
                    $pathToPdf = $this->downloadService->downloadManualFile($manual->getUrl());
                }
                $io->writeln('   pdf: ' . $pathToPdf);
                if (!file_exists($pathToPdf)) {
                    $io->error('file not found: ' . $pathToPdf);
                    continue;
                }
                $pathToCover = $dataDir . $manual->getCovername();
                $io->writeln('   cover: ' . $pathToCover);
                if (!file_exists($pathToCover)) {
                    $io->error('file not found: ' . $pathToCover);
                    continue;
                }

                $manual->setFile(file_get_contents($pathToPdf));
                $manual->setCover(file_get_contents($pathToCover));
                $this->entityManager->persist($manual);
                $this->entityManager->flush();
            } catch (Exception $e) {
                $io->error($e->getMessage());
            }
            $io->newLine();
        }

        $io->success('Finished');

        return Command::SUCCESS;
    }
}
