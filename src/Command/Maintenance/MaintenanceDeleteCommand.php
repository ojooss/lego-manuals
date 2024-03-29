<?php

namespace App\Command\Maintenance;

use App\Repository\SetRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand('app:maintenance:delete', 'remove a set from database')]
class MaintenanceDeleteCommand extends Command
{
    /**
     * MaintenanceDeleteCommand constructor.
     * @param SetRepository $setRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly SetRepository $setRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
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
                throw new RuntimeException('no --number given');
            }

            $set = $this->setRepository->findOneBy(['number' => $number]);
            if (null === $set) {
                throw new RuntimeException('set #'.$number.' not found');
            }

            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Do you really want to remove #'.$set->getNumber().' "'.$set->getName().'"? (y/n)', false, '/^(y|j)/i');
            if (!$helper->ask($input, $output, $question)) {
                $io->warning('Aborted');
                return Command::SUCCESS;
            }

            $this->entityManager->remove($set);
            $this->entityManager->flush();

            $io->success('Set have been removed');
            return Command::SUCCESS;

        } catch (Throwable $t) {
            $io->error($t->getMessage());
            return Command::FAILURE;
        }
    }
}
