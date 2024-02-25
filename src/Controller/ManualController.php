<?php

namespace App\Controller;

use App\Entity\Manual;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ManualController extends AbstractController
{
    #[Route('/manual/{id}', name: 'manual')]
    public function index(Manual $manual): Response
    {
        $filename = strtolower($manual->getFilename());
        $extension = $this->getExtension($filename);

        $response = new Response(
            stream_get_contents(
                $manual->getFile()
            )
        );
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $manual->getFilename(),
            $filename
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/' . $extension);
//        if ($fileSize) {
//            $response->headers->set('Content-Length', $fileSize);
//        }

        return $response;
    }

    #[Route('/manual/{id}/cover', name: 'manual_cover')]
    public function cover(Manual $manual): Response
    {
        $filename = strtolower($manual->getCovername());
        $extension = $this->getExtension($filename);

        $response = new Response(
            stream_get_contents(
                $manual->getCover()
            )
        );
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $manual->getCovername(),
            $filename
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/' . $extension);
//        if ($fileSize) {
//            $response->headers->set('Content-Length', $fileSize);
//        }

        return $response;
    }

    /**
     * @param string $filename
     * @return false|string
     */
    protected function getExtension(string $filename): false|string
    {
        $filename = strtolower($filename);
        $tmp = explode('.', $filename);
        return end($tmp);
    }
}
