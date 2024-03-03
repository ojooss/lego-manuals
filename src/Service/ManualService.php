<?php

namespace App\Service;

use App\Entity\Manual;
use ImagickException;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class ManualService
{
    public function __construct(
        protected ParameterBagInterface $parameterBag,
        protected DownloadService       $downloadService,
        protected PdfService            $pdfService,
    ) {
    }

    /**
     * @throws ImagickException
     * @throws PdfDoesNotExist
     */
    public function fetchFiles(Manual $manual, ?SymfonyStyle $io = null): void
    {
        $missing = 0;
        $pathToPdf = realpath($this->parameterBag->get('app.data_dir')) . '/' . $manual->getPdfFileName();
        if (!file_exists($pathToPdf)) {
            $missing++;
            if ($io) {
                $io->writeln('  - pdf is missing');
                if ($io->isVerbose()) {
                    $io->writeln('    ' . $pathToPdf);
                }
            }
            $this->downloadService->downloadManualFile($manual->getUrl(), $pathToPdf);
        }
        $pathToCover = realpath($this->parameterBag->get('app.data_dir')) . '/' . $manual->getCoverFileName();
        if (!file_exists($pathToCover)) {
            $missing++;
            if ($io) {
                $io->writeln('  - jpg is missing');
                if ($io->isVerbose()) {
                    $io->writeln('    ' . $pathToCover);
                }
            }
            $this->pdfService->extractCover($pathToPdf, $pathToCover);
        }
        if ($io && $missing === 0) {
            $io->writeln('  -> OK');
        }
    }
}
