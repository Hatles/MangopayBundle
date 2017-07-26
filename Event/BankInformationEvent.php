<?php

namespace Troopers\MangopayBundle\Event;

use MangoPay\BankAccount;
use Symfony\Component\EventDispatcher\Event;
use Troopers\MangopayBundle\Entity\BankInformationInterface;
use Troopers\MangopayBundle\Entity\UserInterface;
use Troopers\MangopayBundle\Entity\UserNaturalInterface;

class BankInformationEvent extends Event
{
    /**
     * @var BankAccount
     */
    private $bankAccount;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var BankInformationInterface
     */
    private $bankInformation;

    /**
     * BankInformationEvent constructor.
     * @param BankAccount $bankAccount
     * @param UserInterface $user
     * @param BankInformationInterface $bankInformation
     */
    public function __construct(BankAccount $bankAccount, UserInterface $user, BankInformationInterface $bankInformation)
    {
        $this->bankAccount = $bankAccount;
        $this->user = $user;
        $this->bankInformation = $bankInformation;
    }

    /**
     * @return BankAccount
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * @param BankAccount $bankAccount
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return BankInformationInterface
     */
    public function getBankInformation()
    {
        return $this->bankInformation;
    }

    /**
     * @param BankInformationInterface $bankInformation
     */
    public function setBankInformation($bankInformation)
    {
        $this->bankInformation = $bankInformation;
    }
}
