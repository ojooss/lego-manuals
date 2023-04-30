<?php

namespace App\Controller;

use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * IndexController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $sets = $this->entityManager
            ->getRepository(Set::class)
            ->findBy([], ['name' => 'ASC']);

        $fileCount = 0;
        /** @var Set $set */
        foreach ($sets as $set) {
            $fileCount += $set->getManuals()->count();
        }

        return $this->render('index/index.html.twig', [
            'Sets' => $sets,
            'fileCount' => $fileCount,
        ]);
    }
}
