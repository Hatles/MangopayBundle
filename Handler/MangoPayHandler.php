<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 05/07/2017
 * Time: 14:47
 */

namespace Troopers\MangopayBundle\Handler;

use Doctrine\Common\Annotations\AnnotationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Troopers\MangopayBundle\Annotation\MangoPayField;
use Troopers\MangopayBundle\Entity\BankInformationInterface;
use Troopers\MangopayBundle\Entity\KycDocumentInterface;
use Troopers\MangopayBundle\Entity\TransactionInterface;
use Troopers\MangopayBundle\Entity\UserInterface;
use Troopers\MangopayBundle\Entity\WalletInterface;

class MangoPayHandler
{
    private $accessor;

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();

        $this->container = $container;
    }

    /**
     * @param $entity
     * @param $property
     * @param $mangoEntity
     * @param MangoPayField $annotationField
     * @throws AnnotationException
     */
    public function setFieldFromMangoPayEntity($entity, $property, $mangoEntity, MangoPayField $annotationField)
    {
        $callback = $annotationField->getLoadableCallback();

        if (is_string($callback)) {
            if (method_exists($entity, $callback) && $entity->$callback()) {
                $this->accessor->setValue($entity, $property, $this->getValueFromMangoPayEntity($property, $mangoEntity, $annotationField));
            } else {
                throw new AnnotationException("Loadable callback '" . $callback . "' doesn't exists");
            }
        } elseif ($callback === null || $callback === true) {
            $this->accessor->setValue($entity, $property, $this->getValueFromMangoPayEntity($property, $mangoEntity, $annotationField));
        }
    }

    /**
     * @param string $property
     * @param object|array $mangoEntity
     * @param MangoPayField $annotationField
     * @return mixed|null ;
     */
    private function getValueFromMangoPayEntity($property, $mangoEntity, MangoPayField $annotationField)
    {
        $mangoProperty = $annotationField->getName() ?: ucfirst($property);

        if (property_exists($mangoEntity, $mangoProperty)) {
            return $mangoEntity->{$mangoProperty};
        } else {
            //TODO : throw exception invalid property
            return null;
        }

//        throw new \InvalidArgumentException('L\'entity de la classe "'.get_class($entity).'" doit étendre une des interfaces suivantes : UserInterface, WalletInterface, BankInformationInterface');
    }

    public function disableEntity($entity)
    {
        //TODO : disable mangoPay entity on remove doctrine entity

        switch (true) {
//            case $entity instanceof UserInterface:
//                return $this->container->get('troopers_mangopay.user_helper')->updateOrPersistMangoUser($entity);
//            case $entity instanceof WalletInterface:
//                return $this->container->get('troopers_mangopay.wallet_helper')->updateOrPersistWallet($entity);
            case $entity instanceof BankInformationInterface:
                return $this->container->get('troopers_mangopay.bank_information_helper')->disableBankAccount($entity);
        }

        throw new \InvalidArgumentException('L\'entity de la classe "' . get_class($entity) . '" doit étendre une des interfaces suivantes : UserInterface, WalletInterface, BankInformationInterface');

    }

    /**
     * @param $entity
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\BankAccount|\MangoPay\UserLegal|\MangoPay\UserNatural|\MangoPay\Wallet
     */
    public function getMangoPayEntity($entity, $inLiveCycleCallback = false)
    {
        switch (true) {
            case $entity instanceof UserInterface:
                return $this->container->get('troopers_mangopay.user_helper')->findOrCreateMangoUser($entity, $inLiveCycleCallback);
            case $entity instanceof WalletInterface:
                return $this->container->get('troopers_mangopay.wallet_helper')->findOrCreateWallet($entity, $inLiveCycleCallback);
            case $entity instanceof BankInformationInterface:
                return $this->container->get('troopers_mangopay.bank_information_helper')->findOrCreateBankAccount($entity, $inLiveCycleCallback);
            case $entity instanceof TransactionInterface:
                return $this->container->get('troopers_mangopay.transaction_helper')->findTransaction($entity);
            case $entity instanceof KycDocumentInterface:
                return $this->container->get('troopers_mangopay.kyc_helper')->findKycDocument($entity);
        }

        throw new \InvalidArgumentException('L\'entity de la classe "' . get_class($entity) . '" doit étendre une des interfaces suivantes : UserInterface, WalletInterface, BankInformationInterface');
    }

    public function prePersistOrUpdateMangoPayEntity($entity)
    {
        switch (true) {
            case $entity instanceof UserInterface:
                return $this->container->get('troopers_mangopay.user_helper')->updateOrPersistMangoUser($entity);
            case $entity instanceof WalletInterface:
                return $this->container->get('troopers_mangopay.wallet_helper')->updateOrPersistWallet($entity);
            case $entity instanceof BankInformationInterface:
                return $this->container->get('troopers_mangopay.bank_information_helper')->updateOrPersistBankAccount($entity);
        }

        throw new \InvalidArgumentException('L\'entity de la classe "' . get_class($entity) . '" doit étendre une des interfaces suivantes : UserInterface, WalletInterface, BankInformationInterface');
    }
}