<?php

namespace Troopers\MangopayBundle\Event;

use MangoPay\Wallet;
use Symfony\Component\EventDispatcher\Event;
use Troopers\MangopayBundle\Entity\UserInterface;
use Troopers\MangopayBundle\Entity\UserNaturalInterface;
use Troopers\MangopayBundle\Entity\WalletInterface;

class WalletEvent extends Event
{
    /**
     * @var Wallet
     */
    private $mangoWallet;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var WalletInterface
     */
    private $wallet;

    /**
     * WalletEvent constructor.
     * @param Wallet $mangoWallet
     * @param UserInterface $user
     * @param WalletInterface $wallet
     */
    public function __construct(Wallet $mangoWallet, UserInterface $user, WalletInterface $wallet)
    {
        $this->mangoWallet = $mangoWallet;
        $this->user = $user;
        $this->wallet = $wallet;
    }

    /**
     * Get wallet.
     *
     * @return string
     */
    public function getMangoWallet()
    {
        return $this->mangoWallet;
    }

    /**
     * Set wallet.
     *
     * @param string $mangoWallet
     *
     * @return $this
     */
    public function setMangoWallet($mangoWallet)
    {
        $this->mangoWallet = $mangoWallet;

        return $this;
    }

    /**
     * Get user.
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param UserInterface $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return WalletInterface
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * @param WalletInterface $wallet
     */
    public function setWallet($wallet)
    {
        $this->wallet = $wallet;
    }
}
