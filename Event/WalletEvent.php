<?php

namespace Troopers\MangopayBundle\Event;

use MangoPay\Wallet;
use Symfony\Component\EventDispatcher\Event;
use Troopers\MangopayBundle\Entity\UserNaturalInterface;
use Troopers\MangopayBundle\Entity\WalletInterface;

class WalletEvent extends Event
{
    /**
     * @var Wallet
     */
    private $mangoWallet;

    /**
     * @var UserNaturalInterface
     */
    private $user;

    /**
     * @var WalletInterface
     */
    private $wallet;

    /**
     * WalletEvent constructor.
     * @param Wallet $mangoWallet
     * @param UserNaturalInterface $user
     * @param WalletInterface $wallet
     */
    public function __construct(Wallet $mangoWallet, UserNaturalInterface $user, WalletInterface $wallet)
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
     * @return UserNaturalInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param UserNaturalInterface $user
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
