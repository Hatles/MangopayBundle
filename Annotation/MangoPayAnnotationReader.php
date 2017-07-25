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
use Doctrine\Common\Util\ClassUtils;

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
     * Liste les champs mangopay d'une entité (sous forme de tableau associatif)
     *
     * @param Object
     * @return array
     */
    public function getMangoPayEntityFields($entity)
    {
        $reflection = new \ReflectionClass(get_class($entity));

        if (!$this->isMangoPayEntity($entity)) {
            return [];
        }

        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, MangoPayField::class);
            if ($annotation !== null) {
                $properties[$property->getName()] = $annotation;
            }
        }
        return $properties;
    }

    /**
     * @param $entity
     * @return null|MangoPayEntity
     */
    public function isMangoPayEntity($entity)
    {
        $class = ClassUtils::getClass($entity);
        $reflection = new \ReflectionClass($class);
        return $this->reader->getClassAnnotation($reflection, MangoPayEntity::class);
    }

    public function isMangoPayEntityPersistableOrUpdatable($entity)
    {
        if ($annotation = $this->isMangoPayEntity($entity)) {
            return $annotation->getSupportPersistAndUpdate();
        } else {
            return false;
        }
    }
}
