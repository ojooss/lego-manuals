<?php


namespace App\Service;


use RuntimeException;

class DownloadService
{

    /**
     * @param string $url
     * @param string|null $filePath
     * @return string
     */
    public function downloadManualFile(string $url, ?string $filePath = null): string
    {
        $fileContent = file_get_contents($url);
        if (false === $fileContent) {
            throw new RuntimeException('Can not download: ' . $url);
        }
        $fileName = strtolower(basename($url));
        if (!str_ends_with($fileName, 'pdf')) {
            #throw new RuntimeException('File is not a PDF: ' . $fileName);
            $fileName = $fileName.'.pdf';
        }

        if (null === $filePath) {
            $fileName = $this->getSaveFilename($fileName);
            $filePath = sys_get_temp_dir() . '/' . $fileName;
        }
        if (false === file_put_contents($filePath, $fileContent)) {
            throw new RuntimeException('Can not save ' . $fileName);
        }

        return $filePath;
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
