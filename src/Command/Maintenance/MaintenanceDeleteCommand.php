<?php

namespace App\Command\Maintenance;

use App\Repository\ManualRepository;
use App\Repository\SetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class MaintenanceDeleteCommand extends Command
{
    protected static $defaultName = 'app:maintenance:delete';

    /**
     * @var SetRepository
     */
    private SetRepository $setRepository;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * MaintenanceDeleteCommand constructor.
     * @param SetRepository $setRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(SetRepository $setRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setRepository = $setRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('remove a set from database')
            ->addOption('number', null, InputOption::VALUE_REQUIRED, 'Lego ^set number')
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

        try {
            if ($input->getOption('number')) {
                $number = $input->getOption('number');
            } else {
                throw new \RuntimeException('no --number given');
            }

            $set = $this->setRepository->findOneBy(['number' => $number]);
            if (null === $set) {
                throw new \RuntimeException('set #'.$number.' not found');
            }

            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Do you really want to remove #'.$set->getNumber().' "'.$set->getName().'"? (y/n)', false, '/^(y|j)/i');
            if (!$helper->ask($input, $output, $question)) {
                $io->warning('Aborted');
                return 0;
            }

            $this->entityManager->remove($set);
            $this->entityManager->flush();

            $io->success('Set have been removed');
            return 0;

        } catch (\Throwable $t) {
            $io->error($t->getMessage());
            return 1;
        }

    }

}
