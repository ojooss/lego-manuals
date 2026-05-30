<?php

namespace App\Controller;

use BretRZaun\StatusPage\Check\CallbackCheck;
use BretRZaun\StatusPage\Check\DoctrineConnectionCheck;
use BretRZaun\StatusPage\Check\DoctrineMigrationCheck;
use BretRZaun\StatusPage\Check\PhpExtensionCheck;
use BretRZaun\StatusPage\Check\PhpVersionCheck;
use BretRZaun\StatusPage\Result;
use BretRZaun\StatusPage\StatusChecker;
use BretRZaun\StatusPage\StatusCheckerGroup;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\DependencyFactory;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

class StatusController extends AbstractController
{

    public function __construct(
        #[autowire(service: 'doctrine.migrations.dependency_factory')]
        private readonly DependencyFactory $dependencyFactory,
        private readonly Connection $connection,
        private readonly LoggerInterface   $statusLogger,
        private readonly KernelInterface $kernel,
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route('/status', name: 'app_status')]
    public function index(): Response
    {
        $checker = new StatusChecker();

        $systemGroup = new StatusCheckerGroup('System');
        $checker->addGroup($systemGroup);
        $systemGroup->addCheck(
            new CallbackCheck(
                'security:check',
                function (Result $result) {
                    $process = new Process(['symfony', 'security:check', '--format=json']);
                    $process->setWorkingDirectory(__DIR__ . '/../../');
                    $process->run();
                    if (!$process->isSuccessful()) {
                        throw new RuntimeException($process->getOutput() . PHP_EOL . $process->getErrorOutput());
                    }
                    $json = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
                    if (!empty($json)) {
                        throw new RuntimeException($process->getOutput());
                    }
                }
            )
        );

        $pathToComposerJson = __DIR__ . '/../../composer.json';
        if (!file_exists($pathToComposerJson)) {
            throw new RuntimeException('no composer.json found');
        }
        $json = file_get_contents($pathToComposerJson);
        if (false === $json) {
            throw new RuntimeException('can not read contents from composer.json');
        }
        $composer = json_decode(
            file_get_contents($pathToComposerJson),
            true, 512, JSON_THROW_ON_ERROR);
        if (!isset($composer['require'])) {
            throw new RuntimeException('"require" not found in composer.json');
        }
        foreach ($composer['require'] as $key => $req) {
            $key = strtolower((string) $key);
            # PHP-Extensions
            if (str_starts_with($key, 'ext-')) {
                $extension = str_replace('ext-', '', $key);
                $systemGroup->addCheck(new PhpExtensionCheck(ucfirst($extension), $extension));
            }
            # PHP-Version
            if ($key === 'php') {
                $systemGroup->addCheck(new PhpVersionCheck('php version', $req));
            }
        }


        $connectionGroup = new StatusCheckerGroup('connections');
        $checker->addGroup($connectionGroup);
        //check database connection
        $connectionGroup->addCheck(
            new DoctrineConnectionCheck('database', $this->connection)
        );
        //check doctrine migration status
        $connectionGroup->addCheck(
            new DoctrineMigrationCheck('migrations', $this->dependencyFactory)
        );

        // run checks
        $checker->check();
        $response = $this->render(
            '@status/status.twig',
            [
                'results' => $checker->getResults(),
                'title' => 'Application Status - ' . strtoupper($this->kernel->getEnvironment()),
                'showDetails' => true,
            ]
        );

        // log errors
        if ($checker->hasErrors()) {
            foreach ($checker->getResults() as $statusCheckerGroup) {
                foreach($statusCheckerGroup->getResults() as $result) {
                    if (!$result->isSuccess()) {
                        $this->statusLogger->error('STATUS-ERROR: ' . $result->getLabel() . ': ' . $result->getError());
                    }
                }
            }
        } else {
            $this->statusLogger->info('STATUS: OK');
        }

        $response->setStatusCode($checker->hasErrors()?Response::HTTP_SERVICE_UNAVAILABLE:Response::HTTP_OK);

        return $response;
    }
}
