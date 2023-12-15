<?php


namespace App\Service;


use Imagick;
use ImagickException;
use RuntimeException;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf as PdfImager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PdfService
{
    private string $dataDir;

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
     * @param $pathToPdf
     * @return string
     * @throws ImagickException
     * @throws PdfDoesNotExist
     */
    public function extractCover($pathToPdf): string
    {
        if (!file_exists($pathToPdf)) {
            $pathToPdf = $this->getDataDir() . '/' . $pathToPdf;
        }

        $pdf = new PdfImager($pathToPdf);
        $pdf->setCompressionQuality(90);
        $imgName = basename((string) $pathToPdf).'.jpg';
        if (!$pdf->saveImage($this->getDataDir() . '/' . $imgName)) {
            throw new RuntimeException('Can not extract cover: ' . $imgName);
        }

        $im = new Imagick($this->getDataDir() . '/' . $imgName);
        $im->scaleImage(300, 0);
        $im->writeImage($this->getDataDir() . '/' . $imgName);

        return $imgName;
    }
}
