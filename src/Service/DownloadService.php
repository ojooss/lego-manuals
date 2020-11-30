<?php


namespace App\Service;


use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class DownloadService
{

    /**
     * @var mixed
     */
    private $dataDir;

    /**
     * PdfService constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->dataDir = $parameterBag->get('data_directory');
    }

    /**
     * @return string
     */
    public function getDataDir(): string
    {
        return $this->dataDir;
    }

    /**
     * @param $url
     * @return string
     */
    public function downloadManualFile($url): string
    {
        $fileContent = file_get_contents($url);
        if (false == $fileContent) {
            throw new RuntimeException('Can not download: ' . $url);
        }
        $fileName = strtolower(basename($url));
        if (substr($fileName, -3, 3) !== 'pdf') {
            throw new RuntimeException('File is not a PDF: ' . $fileName);
        }

        $fileName = preg_replace('/[^a-z0-9]/', '', strtolower($fileName));
        $localFile = $this->getDataDir() . '/' . $fileName;
        if (false == file_put_contents($localFile, $fileContent)) {
            throw new RuntimeException('Can not save ' . $fileName);
        }

        return $fileName;
    }

}
