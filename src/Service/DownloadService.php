<?php


namespace App\Service;


use RuntimeException;

class DownloadService
{

    private array $history = [];

    public function __destruct()
    {
        // clean tmp stuff
        foreach ($this->history as $file) {
            @unlink($file);
        }
    }

    /**
     * @param $url
     * @return string
     */
    public function downloadManualFile($url): string
    {
        $fileContent = file_get_contents($url);
        if (false === $fileContent) {
            throw new RuntimeException('Can not download: ' . $url);
        }
        $fileName = strtolower(basename((string) $url));
        if (!str_ends_with($fileName, 'pdf')) {
            #throw new RuntimeException('File is not a PDF: ' . $fileName);
            $fileName = $fileName.'.pdf';
        }

        $fileName = $this->getSaveFilename($fileName);
        $localFile = sys_get_temp_dir() . '/' . $fileName;
        if (false === file_put_contents($localFile, $fileContent)) {
            throw new RuntimeException('Can not save ' . $fileName);
        }

        $this->history[] = $localFile;

        return $localFile;
    }

    /**
     * @param $unsafeFilename
     * @return string
     */
    public function getSaveFilename($unsafeFilename): string
    {
        return preg_replace('/[^a-z0-9.]/', '', strtolower((string) $unsafeFilename));
    }

}
