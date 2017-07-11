<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 05/07/2017
 * Time: 14:47
 */

namespace Troopers\MangopayBundle\Handler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Troopers\MangopayBundle\Annotation\MangoPayField;
use Troopers\MangopayBundle\Entity\BankInformationInterface;
use Troopers\MangopayBundle\Entity\UserInterface;
use Troopers\MangopayBundle\Entity\WalletInterface;
use Troopers\MangopayBundle\Helper\BankInformationHelper;
use Troopers\MangopayBundle\Helper\UserHelper;
use Troopers\MangopayBundle\Helper\WalletHelper;

class MangoPayHandler
{
    private $accessor;

    private $container;

    public function __construct(ContainerInterface $container) {
        $this->accessor = PropertyAccess::createPropertyAccessor();

        $this->container = $container;
    }

    /**
     * @param array|object $entity
     * @param string $property
     * @param object|array $mangoEntity
     * @param MangoPayField $annotationField
     */
    public function setFieldFromMangoPayEntity($entity, $property, $mangoEntity, MangoPayField $annotationField)
    {
        $this->accessor->setValue($entity, $property, $this->getValueFromMangoPayEntity($mangoEntity, $annotationField));
    }

    public function disableEntity($entity, $property)
    {
        //TODO : disable mangoPay entity on remove doctrine entity
    }

    /**
     * @param object|array $mangoEntity
     * @param MangoPayField $annotationField
     * @return mixed;
     */
    private function getValueFromMangoPayEntity($mangoEntity, MangoPayField $annotationField)
    {
        $property = $annotationField->getName();

        if(property_exists($mangoEntity, $property))
        {
            return $mangoEntity->{$property};
        }
        else
        {
            //TODO : throw exception invalid property
            return null;
        }

//        throw new \InvalidArgumentException('L\'entity de la classe "'.get_class($entity).'" doit étendre une des interfaces suivantes : UserInterface, WalletInterface, BankInformationInterface');
    }

    /**
     * @param object|array $entity
     * @return object|array
     */
    public function getMangoPayEntity($entity)
    {
        switch(true) {
            case $entity instanceof UserInterface:
                return $this->container->get('troopers_mangopay.user_helper')->findOrCreateMangoUser($entity);
            case $entity instanceof WalletInterface:
                return $this->container->get('troopers_mangopay.wallet_helper')->findOrCreateWallet($entity);
            case $entity instanceof BankInformationInterface:
                return $this->container->get('troopers_mangopay.bank_information_helper')->findOrCreateBankAccount($entity);
        }

        throw new \InvalidArgumentException('L\'entity de la classe "'.get_class($entity).'" doit étendre une des interfaces suivantes : UserInterface, WalletInterface, BankInformationInterface');
    }

    public function prePersistOrUpdateMangoPayEntity($entity)
    {
        switch(true) {
            case $entity instanceof UserInterface:
                return $this->container->get('troopers_mangopay.user_helper')->updateOrPersistMangoUser($entity);
            case $entity instanceof WalletInterface:
                return $this->container->get('troopers_mangopay.wallet_helper')->updateOrPersistWallet($entity);
            case $entity instanceof BankInformationInterface:
                return $this->container->get('troopers_mangopay.bank_information_helper')->updateOrPersistBankAccount($entity);
        }

        throw new \InvalidArgumentException('L\'entity de la classe "'.get_class($entity).'" doit étendre une des interfaces suivantes : UserInterface, WalletInterface, BankInformationInterface');
    }
}