<?php


namespace App\Service;


use Imagick;
use ImagickException;
use RuntimeException;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf as PdfImager;

readonly class PdfService
{

    /**
     * @param $pathToPdf
     * @return string
     * @throws ImagickException
     * @throws PdfDoesNotExist
     */
    public function extractCover($pathToPdf): string
    {
        if (!file_exists($pathToPdf)) {
            throw new RuntimeException('file not found: ' . $pathToPdf);
        }

        $pdf = new PdfImager($pathToPdf);
        $pdf->setCompressionQuality(90);
        $imgName = basename((string) $pathToPdf).'.jpg';
        $imgFile = sys_get_temp_dir() . '/' . $imgName;
        if (!$pdf->saveImage($imgFile)) {
            throw new RuntimeException('Can not extract cover: ' . $imgName);
        }

        $im = new Imagick($imgFile);
        $im->scaleImage(300, 0);
        $im->writeImage($imgFile);

        return $imgFile;
    }
}
