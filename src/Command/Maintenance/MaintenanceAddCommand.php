<?php

namespace App\Command\Maintenance;

use App\Entity\Manual;
use App\Entity\Set;
use App\Repository\SetRepository;
use App\Service\DownloadService;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand('app:maintenance:add', 'add a set or manual')]
class MaintenanceAddCommand extends Command
{
    /**
     * MaintenanceDeleteCommand constructor.
     * @param SetRepository $setRepository
     * @param EntityManagerInterface $entityManager
     * @param PdfService $pdfService
     * @param DownloadService $downloadService
     */
    public function __construct(
        private readonly SetRepository $setRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly PdfService $pdfService,
        private readonly DownloadService $downloadService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('number', null, InputOption::VALUE_REQUIRED, 'Lego set number')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'name of new set (required for new set)')
            ->addOption('file', null, InputOption::VALUE_OPTIONAL, 'path pdf file (either file or url required)')
            ->addOption('url', null, InputOption::VALUE_OPTIONAL, 'url to pdf file(either file or url required)')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Add new SET or add manuals to existing SET');

        try {

            $number = $input->getOption('number');
            $name = $input->getOption('name');
            $file = $input->getOption('file');
            $url = $input->getOption('url');

            if (empty($number)) {
                throw new RuntimeException('No --number given');
            }

            // check set
            $set = $this->setRepository->findOneBy(['number' => $number]);
            if (null === $set) {
                $set = new Set();
                $set->setNumber($number);
                if (empty($name)) {
                    throw new RuntimeException('No --name given');
                }
                $set->setName($name);
                $io->writeln('New SET has been added');
            } else {
                $io->writeln('Using existing SET ('.$set->getName().')');
            }

            // add manual
            if ($file) {
                $io->writeln('Going to add file');
                $manual = new Manual();
                $fileName = $this->downloadService->getSaveFilename(basename((string) $file));
                $io->writeln('New filename: ' . $fileName);
                $manual->setFilename($fileName);
                $manual->setFile(
                    file_get_contents($file)
                );
                $imgFile = $this->pdfService->extractCover($file);
                $manual->setCovername(basename($imgFile));
                $manual->setCover(file_get_contents($imgFile));
                $set->addManual($manual);
            } elseif ($url) {
                $io->writeln('Going to add file from URL');
                $manual = new Manual();
                $manual->setUrl($url);
                $filePath = $this->downloadService->downloadManualFile($url);
                $io->writeln('New filename: ' . basename($filePath));
                $manual->setFilename(basename($filePath));
                $manual->setFile(file_get_contents($filePath));
                $imgFile = $this->pdfService->extractCover($filePath);
                $manual->setCovername(basename($imgFile));
                $manual->setCover(file_get_contents($imgFile));
                $set->addManual($manual);
            } else {
                throw new RuntimeException('neither --file nor --url given');
            }

            $this->entityManager->persist($set);
            $this->entityManager->flush();

            $io->success('finished');
            return Command::SUCCESS;

        } catch (Throwable $t) {
            $io->error($t->getMessage());
            return Command::FAILURE;
        }
    }
}
