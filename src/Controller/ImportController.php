<?php

namespace App\Controller;

use App\Entity\Manual;
use App\Entity\Set;
use App\Form\SetFormType;
use App\Repository\SetRepository;
use App\Service\DownloadService;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ImagickException;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{

    /**
     * ImportController constructor.
     * @param EntityManagerInterface $entityManager
     * @param DownloadService $downloadService
     * @param PdfService $pdfService
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DownloadService $downloadService,
        private readonly PdfService $pdfService,
    ) {
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ImagickException
     * @throws PdfDoesNotExist
     */
    #[Route(path: '/import', name: 'import')]
    public function index(Request $request): Response
    {

        $set = new Set();
        $manual = new Manual();
        $set->addManual($manual);
        $form = $this->createForm(SetFormType::class, $set, [
            'attr' => ['id' => 'set_form']
        ]);

        try {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                // check for duplicates
                /** @var SetRepository $setRepository */
                $setRepository = $this->entityManager->getRepository(Set::class);
                $setNumber = ''.$form->get('number')->getData();
                $setName = ''.$form->get('name')->getData();
                if (!$setRepository->doesAlreadyExist($setNumber, $setName)) {
                    // download manuals
                    /** @var Manual $manual */
                    foreach ($set->getManuals() as $manual) {
                        $pdfFile = $this->downloadService->downloadManualFile($manual->getUrl());
                        $manual->setFilename($pdfFile);
                        $jpgFile = $this->pdfService->extractCover($pdfFile);
                        $manual->setCovername($jpgFile);
                    }
                    $this->entityManager->persist($set);
                    $this->entityManager->flush();
                    // goto index
                    return $this->redirectToRoute('index');
                } else {
                    $form->addError(new FormError("Das Set '#" . $setNumber . " " . $setName . "' existiert bereits"));
                }
            }

        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            throw $e;
        }

        return $this->render('import/index.html.twig', [
            'form' => $form,
        ]);
    }

}
