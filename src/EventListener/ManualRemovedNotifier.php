<?php

namespace App\EventListener;

use App\Entity\Manual;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ManualRemovedNotifier
{

    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    /**
     * ManualRemovedNotifier constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * the entity instance and the lifecycle event
     * the entity listener methods receive two arguments:
     *
     * @param Manual $manual
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Manual $manual, LifecycleEventArgs $event): void
    {
        $dataDirectory = $this->parameterBag->get('data_directory');
        foreach([$manual->getFilename(), $manual->getCovername()] as $file) {
            if (file_exists($dataDirectory . '/' . $file) && is_file($dataDirectory . '/' . $file)) {
                if (false === unlink($dataDirectory . '/' . $file)) {
                    throw new RuntimeException('Can not delete ' . $dataDirectory . '/' . $file);
                }
            }
        }
    }

}