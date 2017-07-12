<?php

namespace Troopers\MangopayBundle\Helper;

use Doctrine\ORM\EntityManager;
use MangoPay\Address;
use MangoPay\UserNatural;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Troopers\MangopayBundle\Entity\UserInterface;
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

    public function __construct(MangopayHelper $mangopayHelper, EntityManager $entityManager, EventDispatcherInterface $dispatcher)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param UserInterface $user
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function findOrCreateMangoUser(UserInterface $user)
    {
        if ($mangoUserId = $user->getMangoUserId()) {
            $mangoUser = $this->mangopayHelper->Users->get($mangoUserId);
        } else {
            $mangoUser = $this->createMangoUser($user);
        }

        return $mangoUser;
    }

    /**
     * @param UserInterface $user
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function createMangoUser(UserInterface $user, $inLiveCycleCallback = false)
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
     * @param UserInterface $user
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function updateMangoUser(UserInterface $user, $inLiveCycleCallback = false)
    {
        $birthday = null;
        if ($user->getBirthDay() instanceof \Datetime) {
            $birthday = $user->getBirthDay()->getTimestamp();
        }
        $mangoUserId = $user->getMangoUserId();
        $mangoUser = $this->mangopayHelper->Users->get($mangoUserId);

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
     * @param UserInterface $user
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function updateOrPersistMangoUser(UserInterface $user)
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
