<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 05/07/2017
 * Time: 14:41
 */

namespace Troopers\MangopayBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Troopers\MangopayBundle\Annotation\MangoPayAnnotationReader;
use Troopers\MangopayBundle\Handler\MangoPayHandler;

class MangoPaySubscriber implements EventSubscriber
{
    /**
     * @var MangoPayAnnotationReader
     */
    private $reader;

    /**
     * @var MangoPayHandler
     */
    private $handler;

    public function __construct(MangoPayAnnotationReader $reader, MangoPayHandler $handler)
    {
        $this->reader = $reader;
        $this->handler = $handler;
    }


    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
            'prePersist',
            'preUpdate'
        ];
    }

    public function postLoad(LifecycleEventArgs $event) {
        $entity = $event->getEntity();

        if($this->reader->isMangoPayEntity($entity))
        {
            $mangoEntity = $this->handler->getMangoPayEntity($entity);

            foreach ($this->reader->getMangoPayEntityFields($entity) as $property => $annotation) {
                $this->handler->setFieldFromMangoPayEntity($entity, $property, $mangoEntity, $annotation);
            }
        }
    }

    public function prePersist(LifecycleEventArgs $event) {
        $this->prePersistOrUpdate($event);
    }

    public function preUpdate(LifecycleEventArgs $event) {
        $this->prePersistOrUpdate($event);
    }

    private function prePersistOrUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if($this->reader->isMangoPayEntity($entity))
        {
            $this->handler->prePersistOrUpdateMangoPayEntity($entity);
        }
    }
}