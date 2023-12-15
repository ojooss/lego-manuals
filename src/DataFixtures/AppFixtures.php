<?php

namespace App\DataFixtures;

use App\Entity\Manual;
use App\Entity\Set;
use App\Service\DownloadService;
use App\Service\PdfService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use ImagickException;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;

class AppFixtures extends Fixture
{

    protected array $data = [
        ['60248', 'Einsatz mit dem Feuerwehrhubschrauber', 'https://www.lego.com/cdn/product-assets/product.bi.core.pdf/6310746.pdf'],
        ['31088', 'Bewohner der Tiefsee', 'https://www.lego.com/cdn/product-assets/product.bi.core.pdf/6263536.pdf'],
        ['31088', 'Bewohner der Tiefsee', 'https://www.lego.com/cdn/product-assets/product.bi.core.pdf/6263537.pdf'],
        ['31088', 'Bewohner der Tiefsee', 'https://www.lego.com/cdn/product-assets/product.bi.core.pdf/6263540.pdf'],
        ['31088', 'Bewohner der Tiefsee', 'https://www.lego.com/cdn/product-assets/product.bi.additional.extra.pdf/31088_X_Whale.pdf'],
    ];

    /**
     * AppFixtures constructor.
     * @param DownloadService $downloadService
     * @param PdfService $pdfService
     */
    public function __construct(
        private readonly DownloadService $downloadService,
        private readonly PdfService $pdfService,
    ) {
    }

    /**
     * @throws PdfDoesNotExist
     * @throws ImagickException
     */
    public function load(ObjectManager $manager): void
    {
        $setRepository = $manager->getRepository(Set::class);

        foreach ($this->data as $data) {
            $setNumber = $data[0];
            $setName = $data[1];
            $url = $data[2];

            $set = $setRepository->findOneBy(['name' => $setName]);
            if (null === $set) {
                $set = new Set();
                $set->setName($setName);
                $set->setNumber($setNumber);
                $manager->persist($set);
                $manager->flush();
            }

            $manual = new Manual();
            $manual->setUrl($url);
            $fileName = $this->downloadService->downloadManualFile($url);
            $manual->setFilename($fileName);
            $imgName = $this->pdfService->extractCover(
                $this->pdfService->getDataDir() . '/' . $fileName
            );
            $manual->setCovername($imgName);
            $manual->setSet($set);
            $manager->persist($manual);
        }

        $manager->flush();
    }
}
