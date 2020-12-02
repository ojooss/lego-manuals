<?php

namespace App\Controller;

use App\Entity\Manual;
use App\Entity\Set;
use App\Form\SetFormType;
use App\Repository\SetRepository;
use App\Service\DownloadService;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use ImagickException;
use RuntimeException;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var DownloadService
     */
    private DownloadService $downloadService;

    /**
     * @var PdfService
     */
    private PdfService $pdfService;

    /**
     * ImportController constructor.
     * @param EntityManagerInterface $entityManager
     * @param DownloadService $downloadService
     * @param PdfService $pdfService
     */
    public function __construct(EntityManagerInterface $entityManager, DownloadService $downloadService,PdfService $pdfService)
    {
        $this->entityManager = $entityManager;
        $this->downloadService = $downloadService;
        $this->pdfService = $pdfService;
    }

    /**
     * @Route("/import", name="import")
     * @param Request $request
     * @return Response
     * @throws ImagickException
     * @throws PdfDoesNotExist
     */
    public function index(Request $request): Response
    {

        $set = new Set();
        $manual = new Manual();
        $set->addManual($manual);

        $form = $this->createForm(SetFormType::class, $set);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // check for duplicates
            /** @var SetRepository $setRepository */
            $setRepository = $this->entityManager->getRepository(Set::class);
            $setNumber = $form->get('number')->getData();
            $itemsNumber = $setRepository->findBy(['number' => $setNumber]);
            $setName = $form->get('name')->getData();
            $itemsName = $setRepository->findBy(['name' => $setName]);
            if (!empty($itemsNumber) || !empty($itemsName)) {
                $form->addError(new FormError("Das Set '#".$setNumber." ".$setName."' existiert bereits"));
            }

dump($form->isValid());
dump($form->getErrors());


            // download
            /** @var Manual $manual */
/*
            foreach($set->getManuals() as $manual) {
                $pdfFile = $this->downloadService->downloadManualFile($manual->getUrl());
                $manual->setFilename($pdfFile);
                $jpgFile = $this->pdfService->extractCover($pdfFile);
                $manual->setCovername($jpgFile);
            }

            $this->entityManager->persist($set);
            $this->entityManager->flush();

            return $this->redirectToRoute('index');
*/
        }

        return $this->render('import/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
