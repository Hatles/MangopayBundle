<?php

namespace Troopers\MangopayBundle\Helper;

use Doctrine\ORM\EntityManager;
use MangoPay\BankAccount;
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Troopers\MangopayBundle\Entity\BankInformationInterface;
use Troopers\MangopayBundle\Entity\UserInterface;
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

    public function findOrCreateBankAccount(BankInformationInterface $bankInformation)
    {
        if ($mangoBankAccountId = $bankInformation->getMangoBankAccountId()) {
            $mangoBankAccount = $this->mangopayHelper->Users->GetBankAccount($bankInformation->getUser()->getMangoUserId(), $mangoBankAccountId);
        } else {
            $mangoBankAccount = $this->createBankAccount($bankInformation);
        }

        return $mangoBankAccount;
    }

    public function createBankAccount(BankInformationInterface $bankInformation)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($bankInformation->getUser());
        //Create mango bank account
        $bankAccount = new BankAccount();
        $bankAccount->OwnerName = $bankInformation->getUser()->getFullName();
        $bankAccount->UserId = $mangoUser->Id;
        $bankAccount->Type = 'IBAN';
        $bankAccount->OwnerAddress = $bankInformation->getMangoAddress();

        $bankAccountDetailsIban = new BankAccountDetailsIBAN();
        $bankAccountDetailsIban->IBAN = $bankInformation->getIban();

        $bankAccount->Details = $bankAccountDetailsIban;

        $bankAccount = $this->mangopayHelper->Users->CreateBankAccount($bankInformation->getUser()->getMangoUserId(), $bankAccount);

        $bankInformation->setMangoBankAccountId($bankAccount->Id);

        $event = new BankInformationEvent($bankAccount, $bankInformation->getUser(), $bankInformation);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_BANKINFORMATION, $event);

        $this->entityManager->persist($bankInformation);
        $this->entityManager->flush();

        return $bankAccount;
    }

    public function createBankAccountForUser(UserInterface $user, $iban)
    {
        $bankAccount = new BankAccount();
        $bankAccount->OwnerName = $user->getFullName();
        $bankAccount->UserId = $user->getMangoUserId();
        $bankAccount->Type = 'IBAN';

        $bankAccount->OwnerAddress = $user->getMangoAddress();

        $bankAccountDetailsIban = new BankAccountDetailsIBAN();
        $bankAccountDetailsIban->IBAN = $iban;

        $bankAccount->Details = $bankAccountDetailsIban;

        $bankAccount = $this->mangopayHelper->Users->CreateBankAccount($user->getMangoUserId(), $bankAccount);

        $event = new BankInformationEvent($bankAccount, $user, null);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_BANKINFORMATION_FOR_USER, $event);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $bankAccount;
    }
}
