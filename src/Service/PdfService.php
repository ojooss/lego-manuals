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
     * @param string $pathToPdf
     * @param string|null $pathToJpg
     * @return string
     * @throws ImagickException
     * @throws PdfDoesNotExist
     */
    public function extractCover(string $pathToPdf, ?string $pathToJpg = null): string
    {
        if (!file_exists($pathToPdf)) {
            throw new RuntimeException('file not found: ' . $pathToPdf);
        }

        $pdf = new PdfImager($pathToPdf);
        $pdf->setCompressionQuality(90);
        if (null === $pathToJpg) {
            $imgName = basename($pathToPdf) . '.jpg';
            $pathToJpg = sys_get_temp_dir() . '/' . $imgName;
        }
        if (!$pdf->saveImage($pathToJpg)) {
            throw new RuntimeException('Can not extract cover of : ' . $pathToPdf);
        }

        // scale image down to 300px
        $im = new Imagick($pathToJpg);
        $im->scaleImage(300, 0);
        $im->writeImage($pathToJpg);

        return $pathToJpg;
    }
}
