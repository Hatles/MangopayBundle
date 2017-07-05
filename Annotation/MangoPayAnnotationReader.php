<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 05/07/2017
 * Time: 14:36
 */

namespace Troopers\MangopayBundle\Listener;


use Doctrine\Common\Annotations\AnnotationReader;
use Troopers\MangopayBundle\Annotation\MangoPayEntity;
use Troopers\MangopayBundle\Annotation\MangoPayField;

class MangoPayAnnotationReader
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

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
}