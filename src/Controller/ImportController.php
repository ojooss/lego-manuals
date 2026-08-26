<?php

namespace App\Controller;

use App\Entity\Manual;
use App\Entity\Set;
use App\Form\SetFormType;
use App\Repository\SetRepository;
use App\Service\ManualService;
use App\Service\SetLookupService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{

    /**
     * ImportController constructor.
     * @param EntityManagerInterface $entityManager
     * @param ManualService $manualService
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ManualService $manualService,
        private readonly SetLookupService $setLookupService,
    ) {
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
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
                    foreach ($set->getManuals() as $manual) {
                        if (empty($manual->getUrl())) {
                            $set->removeManual($manual);
                        }
                    }
                    $this->entityManager->persist($set);
                    $this->entityManager->flush();
                    foreach ($set->getManuals() as $manual) {
                        $this->manualService->fetchFiles($manual);
                    }
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

    /**
     * @param int $setNumber
     * @return Response
     */
    #[Route(path: '/import/autoload/{setNumber}', name: 'import_autoload')]
    public function autoload(int $setNumber): Response
    {
        try {
            return new JsonResponse($this->setLookupService->lookup($setNumber));
        } catch (ProcessFailedException $exception) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }
}
