<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class SetLookupService
{
    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
    }

    /**
     * Runs the Puppeteer-based scraper script to look up a LEGO set's
     * title and manual URLs on lego.com by its set number.
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function lookup(int $setNumber): array
    {
        $script = $this->kernel->getProjectDir() . '/try-to-get-pdf-urls.js';
        $process = new Process(['node', $script, '--set', $setNumber]);
        $process->mustRun();

        return json_decode($process->getOutput(), true) ?? [];
    }
}
