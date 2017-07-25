<?php

namespace Troopers\MangopayBundle\Helper;

use Doctrine\ORM\EntityManager;
use MangoPay\BankAccount;
use MangoPay\BankAccountDetailsIBAN;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Troopers\MangopayBundle\Entity\BankInformationInterface;
use Troopers\MangopayBundle\Entity\UserNaturalInterface;
use Troopers\MangopayBundle\Event\BankInformationEvent;
use Troopers\MangopayBundle\TroopersMangopayEvents;

/**
 * ref: troopers_mangopay.bank_information_helper.
 **/
class BankInformationHelper
{
    /**
     * @var MangopayHelper
     */
    private $mangopayHelper;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * BankInformationHelper constructor.
     * @param MangopayHelper $mangopayHelper
     * @param EntityManager $entityManager
     * @param UserHelper $userHelper
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(MangopayHelper $mangopayHelper, EntityManager $entityManager, UserHelper $userHelper, EventDispatcherInterface $dispatcher)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->userHelper = $userHelper;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    public function findOrCreateBankAccount(BankInformationInterface $bankInformation, $inLiveCycleCallback = false)
    {
        $mangoBankAccount = null;

        if ($mangoBankAccountId = $bankInformation->getMangoBankAccountId()) {
            $mangoBankAccount = $this->mangopayHelper->Users->GetBankAccount($bankInformation->getUser()->getMangoUserId(), $mangoBankAccountId);
        } elseif (!$inLiveCycleCallback) {
            $mangoBankAccount = $this->createBankAccount($bankInformation);
        }

        return $mangoBankAccount;
    }

    public function createBankAccount(BankInformationInterface $bankInformation, $inLiveCycleCallback = false)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($bankInformation->getUser());
        //Create mango bank account
        $bankAccount = new BankAccount();
        $bankAccount->OwnerName = $bankInformation->getOwnerName();
        $bankAccount->UserId = $mangoUser->Id;
        $bankAccount->Type = 'IBAN';
        $bankAccount->OwnerAddress = $bankInformation->getMangoAddress();

        $bankAccountDetailsIban = new BankAccountDetailsIBAN();
        $bankAccountDetailsIban->IBAN = $bankInformation->getIban();

        $bankAccount->Details = $bankAccountDetailsIban;

        $bankAccount = $this->mangopayHelper->Users->CreateBankAccount($mangoUser->Id, $bankAccount);

        $bankInformation->setMangoBankAccountId($bankAccount->Id);

        dump($bankAccount);
        dump($bankInformation);

        $event = new BankInformationEvent($bankAccount, $bankInformation->getUser(), $bankInformation);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_BANKINFORMATION, $event);

        if (!$inLiveCycleCallback) {
            $this->entityManager->persist($bankInformation);
            $this->entityManager->flush();
        }

        return $bankAccount;
    }

    public function createBankAccountForUser(UserNaturalInterface $user, $iban)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($user);

        $bankAccount = new BankAccount();
        $bankAccount->OwnerName = $user->getFullName();
        $bankAccount->UserId = $mangoUser->Id;
        $bankAccount->Type = 'IBAN';

        $bankAccount->OwnerAddress = $user->getMangoAddress();

        $bankAccountDetailsIban = new BankAccountDetailsIBAN();
        $bankAccountDetailsIban->IBAN = $iban;


        $bankAccount->Details = $bankAccountDetailsIban;

        $bankAccount = $this->mangopayHelper->Users->CreateBankAccount($mangoUser->Id, $bankAccount);

        $event = new BankInformationEvent($bankAccount, $user, null);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_BANKINFORMATION_FOR_USER, $event);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $bankAccount;
    }

    /**
     * @param BankInformationInterface $bankInformation
     * @return BankAccount
     */
    public function updateOrPersistBankAccount(BankInformationInterface $bankInformation)
    {
        if (!$bankInformation->getMangoBankAccountId()) {
            return $this->createBankAccount($bankInformation, true);
        }
        // cant update a bank account activated
//        else
//        {
//            return $this->updateBankAccount($bankInformation, true);
//        }
    }

    public function disableBankAccount(BankInformationInterface $bankInformation)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($bankInformation->getUser());

        $bankAccountId = $bankInformation->getMangoBankAccountId();
        $bankAccount = $this->mangopayHelper->Users->GetBankAccount($mangoUser->Id, $bankAccountId);

        //desactivate before update
        $bankAccount->Active = false;

        $bankAccount = $this->mangopayHelper->Users->UpdateBankAccount($mangoUser->Id, $bankAccount);

        $event = new BankInformationEvent($bankAccount, $bankInformation->getUser(), $bankInformation);
        $this->dispatcher->dispatch(TroopersMangopayEvents::DISABLE_BANKINFORMATION, $event);

        return $bankAccount;
    }

    private function updateBankAccount(BankInformationInterface $bankInformation, $inLiveCycleCallback = false)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($bankInformation->getUser());

        $bankAccountId = $bankInformation->getMangoBankAccountId();
        $bankAccount = $this->mangopayHelper->Users->GetBankAccount($mangoUser->Id, $bankAccountId);

        //desactivate before update
        $bankAccount->Active = false;
        $bankAccount->OwnerName = $bankInformation->getOwnerName();
        $bankAccount->UserId = $mangoUser->Id;
        $bankAccount->Type = 'IBAN';
        $bankAccount->OwnerAddress = $bankInformation->getMangoAddress();

        $bankAccountDetailsIban = new BankAccountDetailsIBAN();
        $bankAccountDetailsIban->IBAN = $bankInformation->getIban();

        $bankAccount->Details = $bankAccountDetailsIban;

//        try
//        {
        $bankAccount = $this->mangopayHelper->Users->UpdateBankAccount($mangoUser->Id, $bankAccount);

        //reactivate
        $bankAccount->Active = true;
        $bankAccount = $this->mangopayHelper->Users->UpdateBankAccount($mangoUser->Id, $bankAccount);

        $event = new BankInformationEvent($bankAccount, $bankInformation->getUser(), $bankInformation);
        $this->dispatcher->dispatch(TroopersMangopayEvents::UPDATE_BANKINFORMATION, $event);

        if (!$inLiveCycleCallback) {
            $this->entityManager->persist($bankInformation);
            $this->entityManager->flush();
        }

        return $bankAccount;
//        } catch (ResponseException $e) {
//
//            Logs::Debug('MangoPay\ResponseException Code', $e->GetCode());
//            Logs::Debug('Message', $e->GetMessage());
//            Logs::Debug('Details', $e->GetErrorDetails());
//            1/0;
//
//        }
    }
}
