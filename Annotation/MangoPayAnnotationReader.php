<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 05/07/2017
 * Time: 14:36
 */

namespace Troopers\MangopayBundle\Annotation;


use Application\Sonata\ClassificationBundle\Entity\Collection;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Troopers\MangopayBundle\Annotation\MangoPayEntity;
use Troopers\MangopayBundle\Annotation\MangoPayField;

class MangoPayAnnotationReader
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var Collection
     */
    private $linkedEntities;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
        $this->linkedEntities = new ArrayCollection();
    }


    /**
     * @param $entity
     * @return bool
     */
    public function isLinkedToMangoPayEntity($entity)
    {
        return $this->linkedEntities->contains($entity);
    }

    /**
     * @param object $entity
     */
    public function addLinkedEntity($entity)
    {
        $this->linkedEntities->add($entity);
    }

    /**
     * @param $entity
     * @return null|MangoPayEntity
     */
    public function isMangoPayEntity($entity)
    {
        $reflection = new \ReflectionClass(get_class($entity));
        return $this->reader->getClassAnnotation($reflection, MangoPayEntity::class);
    }

    /**
     * Liste les champs mangopay d'une entité (sous forme de tableau associatif)
     *
     * @param Object
     * @return array
     */
    public function getMangoPayEntityFields($entity) {
        $reflection = new \ReflectionClass(get_class($entity));

        if(!$this->isMangoPayEntity($entity))
        {
            return [];
        }

        $properties = [];
        foreach($reflection->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, MangoPayField::class);
            if ($annotation !== null) {
                $properties[$property->getName()] = $annotation;
            }
        }
        return $properties;
    }

    public function isMangoPayEntityPersistableOrUpdatable($entity)
    {
        if($annotation = $this->isMangoPayEntity($entity))
        {
            return $annotation->getSupportPersistAndUpdate();
        }
        else
        {
            return false;
        }
    }
}