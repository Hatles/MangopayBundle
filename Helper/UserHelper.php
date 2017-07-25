<?php

namespace Troopers\MangopayBundle\Helper;

use Doctrine\ORM\EntityManager;
use MangoPay\Address;
use MangoPay\UserNatural;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Troopers\MangopayBundle\Entity\UserInterface;
use Troopers\MangopayBundle\Entity\UserLegalInterface;
use Troopers\MangopayBundle\Entity\UserNaturalInterface;
use Troopers\MangopayBundle\Event\UserEvent;
use Troopers\MangopayBundle\TroopersMangopayEvents;

/**
 * ref: troopers_mangopay.user_helper.
 **/
class UserHelper
{
    private $mangopayHelper;
    private $entityManager;
    private $dispatcher;
    private $userLegalHelper;

    public function __construct(MangopayHelper $mangopayHelper, EntityManager $entityManager, EventDispatcherInterface $dispatcher, UserLegalHelper $userLegalHelper)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->userLegalHelper = $userLegalHelper;
    }

    /**
     * @param UserInterface $user
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function findOrCreateMangoUser(UserInterface $user, $inLiveCycleCallback = false)
    {
        $mangoUser = null;

        if ($mangoUserId = $user->getMangoUserId()) {
            $mangoUser = $this->mangopayHelper->Users->get($mangoUserId);
        } elseif(!$inLiveCycleCallback) {
            if($user instanceof UserNaturalInterface)
            {
                $mangoUser = $this->createMangoUser($user, $inLiveCycleCallback);
            }
            elseif ($user instanceof UserLegalInterface)
            {
                $mangoUser = $this->userLegalHelper->createMangoUserLegal($user, $inLiveCycleCallback);
            }
        }

        return $mangoUser;
    }

    /**
     * @param UserNaturalInterface $user
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function findOrCreateMangoUserNatural(UserNaturalInterface $user, $inLiveCycleCallback = false)
    {
        $mangoUser = null;

        if ($mangoUserId = $user->getMangoUserId()) {
            $mangoUser = $this->mangopayHelper->Users->GetNatural($mangoUserId);
        } elseif(!$inLiveCycleCallback) {
            $mangoUser = $this->createMangoUser($user, $inLiveCycleCallback);
        }

        return $mangoUser;
    }

    /**
     * @param UserNaturalInterface $user
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function createMangoUser(UserNaturalInterface $user, $inLiveCycleCallback = false)
    {
        $birthday = null;
        if ($user->getBirthDay() instanceof \Datetime) {
            $birthday = $user->getBirthDay()->getTimestamp();
        }
        $mangoUser = new UserNatural();
        $mangoUser->Email = $user->getEmail();
        $mangoUser->FirstName = $user->getFirstname();
        $mangoUser->LastName = $user->getLastname();
        $mangoUser->Birthday = $birthday;
        $mangoUser->Nationality = $user->getNationalityCode();
        $mangoUser->CountryOfResidence = $user->getCountryCode();
        $mangoUser->Tag = $user->getId();

        $mangoUser->Address = $user->getMangoAddress();

        $mangoUser = $this->mangopayHelper->Users->Create($mangoUser);

        $user->setMangoUserId($mangoUser->Id);

        $event = new UserEvent($user, $mangoUser);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_USER, $event);

        if(!$inLiveCycleCallback) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $mangoUser;
    }

    /**
     * @param UserNaturalInterface $user
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function updateMangoUser(UserNaturalInterface $user, $inLiveCycleCallback = false)
    {
        $birthday = null;
        if ($user->getBirthDay() instanceof \Datetime) {
            $birthday = $user->getBirthDay()->getTimestamp();
        }
        $mangoUserId = $user->getMangoUserId();
        $mangoUser = $this->mangopayHelper->Users->GetNatural($mangoUserId);

        $mangoUser->Email = $user->getEmail();
        $mangoUser->FirstName = $user->getFirstname();
        $mangoUser->LastName = $user->getLastname();
        $mangoUser->Birthday = $birthday;
        $mangoUser->Nationality = $user->getNationalityCode();
        $mangoUser->CountryOfResidence = $user->getCountryCode();
        $mangoUser->Tag = $user->getId();

        $mangoUser->Address = $user->getMangoAddress();

        $mangoUser = $this->mangopayHelper->Users->Update($mangoUser);

        if(!$inLiveCycleCallback)
        {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $mangoUser;
    }

    /**
     * @param UserNaturalInterface $user
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function updateOrPersistMangoUserNatural(UserNaturalInterface $user)
    {
        if(!$user->getId() or !$user->getMangoUserId())
        {
            return $this->createMangoUser($user, true);
        }
        else
        {
            return $this->updateMangoUser($user, true);
        }
    }

    /**
     * @param UserInterface $user
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function updateOrPersistMangoUser(UserInterface $user)
    {

        if(!$user->getId() or !$user->getMangoUserId())
        {
            if($user instanceof UserNaturalInterface)
            {
                return $this->createMangoUser($user, true);
            }
            elseif ($user instanceof UserLegalInterface)
            {
                return $this->userLegalHelper->createMangoUserLegal($user, true);
            }
        }
        else
        {
            if($user instanceof UserNaturalInterface)
            {
                return $this->updateMangoUser($user, true);
            }
            elseif ($user instanceof UserLegalInterface)
            {
                return $this->userLegalHelper->updateMangoUserLegal($user, true);
            }
        }

        return null;
    }

    public function getTransactions($userId)
    {
        return $this->mangopayHelper->Users->GetTransactions($userId);
    }

    public function getWallets($userId)
    {
        return $this->mangopayHelper->Users->GetWallets($userId);
    }

    public function getBankAccounts($userId)
    {
        return $this->mangopayHelper->Users->GetBankAccounts($userId);
    }
}
