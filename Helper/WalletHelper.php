<?php

namespace Troopers\MangopayBundle\Helper;

use Doctrine\ORM\EntityManager;
use MangoPay\Wallet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Troopers\MangopayBundle\Entity\UserNaturalInterface;
use Troopers\MangopayBundle\Entity\WalletInterface;
use Troopers\MangopayBundle\Event\WalletEvent;
use Troopers\MangopayBundle\TroopersMangopayEvents;

/**
 * ref: troopers_mangopay.wallet_helper.
 **/
class WalletHelper
{
    /**
     * @var MangopayHelper
     */
    private $mangopayHelper;

    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * WalletHelper constructor.
     * @param MangopayHelper $mangopayHelper
     * @param UserHelper $userHelper
     * @param EntityManager $entityManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(MangopayHelper $mangopayHelper, UserHelper $userHelper, EntityManager $entityManager, EventDispatcherInterface $dispatcher)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->userHelper = $userHelper;
        $this->dispatcher = $dispatcher;
        $this->entityManager = $entityManager;
    }

    /**
     * @param UserNaturalInterface $user
     * @param string        $description
     * @param string        $currency
     *
     * @return Wallet
     */
    public function findOrCreateWalletWithCurrency(UserNaturalInterface $user, $currency, $description = 'current wallet')
    {
        $walletCur = null;

        foreach ($user->getWallets() as $wallet)
        {
            if ($walletId = $wallet->getMangoWalletId()) {
                $walletTmp = $this->mangopayHelper->Wallets->get($walletId);

                if($walletTmp->Currency == $currency)
                {
                    $walletCur = $walletTmp;
                    break;
                }
            }
        }

        if (!$walletCur) {
            $walletCur = $this->createWalletForUser($user, $currency, $description);
        }

        return $walletCur;
    }

    /**
     * @param WalletInterface $wallet
     * @param bool $inLiveCycleCallback
     * @return Wallet|null
     */
    public function findOrCreateWallet(WalletInterface $wallet, $inLiveCycleCallback = false)
    {
        $mangoWallet = null;

        if ($walletId = $wallet->getMangoWalletId()) {
            $mangoWallet = $this->mangopayHelper->Wallets->get($walletId);
        }
        elseif(!$inLiveCycleCallback) {
            $mangoWallet = $this->createWallet($wallet);
        }

        return $mangoWallet;
    }

    /**
     * @param UserNaturalInterface $user
     * @param string $currency
     * @param string $description
     * @return Wallet
     */
    public function createWalletForUser(UserNaturalInterface $user, $currency, $description = 'current wallet')
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($user);
        $mangoWallet = new Wallet();
        $mangoWallet->Owners = [$mangoUser->Id];
        $mangoWallet->Currency = $currency;
        $mangoWallet->Description = $description;

        $mangoWallet = $this->mangopayHelper->Wallets->create($mangoWallet);

        $event = new WalletEvent($mangoWallet, $user, null);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_WALLET_FOR_USER, $event);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $mangoWallet;
    }

    /**
     * @param WalletInterface $wallet
     * @param bool $inLiveCycleCallback
     * @return Wallet
     */
    public function createWallet(WalletInterface $wallet, $inLiveCycleCallback = false)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($wallet->getUser());
        $mangoWallet = new Wallet();
        $mangoWallet->Owners = [$mangoUser->Id];
        $mangoWallet->Currency = $wallet->getCurrencyCode();
        $mangoWallet->Description = $wallet->getDescription();

        $mangoWallet = $this->mangopayHelper->Wallets->create($mangoWallet);

        $wallet->setMangoWalletId($mangoWallet->Id);

        $event = new WalletEvent($mangoWallet, $wallet->getUser(), $wallet);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_WALLET, $event);

        if(!$inLiveCycleCallback) {
            $this->entityManager->persist($wallet);
            $this->entityManager->flush();
        }

        return $mangoWallet;
    }

    /**
     * @param WalletInterface $wallet
     * @param bool $inLiveCycleCallback
     * @return Wallet
     */
    public function updateWallet(WalletInterface $wallet, $inLiveCycleCallback = false)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($wallet->getUser());

        $mangoWalletID = $wallet->getMangoWalletId();
        $mangoWallet = $this->mangopayHelper->Wallets->Get($mangoWalletID);
        $mangoWallet->Owners = [$mangoUser->Id];
        $mangoWallet->Currency = $wallet->getCurrencyCode();
        $mangoWallet->Description = $wallet->getDescription();

        $mangoWallet = $this->mangopayHelper->Wallets->Update($mangoWallet);

        $event = new WalletEvent($mangoWallet, $wallet->getUser(), $wallet);
        $this->dispatcher->dispatch(TroopersMangopayEvents::UPDATE_WALLET, $event);

        if(!$inLiveCycleCallback) {
            $this->entityManager->persist($wallet);
            $this->entityManager->flush();
        }

        return $mangoWallet;
    }

    /**
     * @param WalletInterface $wallet
     * @return \MangoPay\Wallet
     */
    public function updateOrPersistWallet(WalletInterface $wallet)
    {
        if(!$wallet->getMangoWalletId())
        {
            return $this->createWallet($wallet, true);
        }
        else
        {
            return $this->updateWallet($wallet, true);
        }
    }

    public function getTransactions($walletId)
    {
        return $this->mangopayHelper->Wallets->GetTransactions($walletId);
    }
}
