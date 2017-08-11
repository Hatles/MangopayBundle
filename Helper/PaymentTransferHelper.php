<?php

namespace Troopers\MangopayBundle\Helper;

use Doctrine\ORM\EntityManager;
use MangoPay\Money;
use MangoPay\PayOut;
use MangoPay\PayOutPaymentDetailsBankWire;
use MangoPay\Transfer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Troopers\MangopayBundle\Entity\BankInformationInterface;
use Troopers\MangopayBundle\Entity\TransactionInterface;

/**
 * ref: troopers_mangopay.payment_transfer_helper.
 **/
class PaymentTransferHelper
{
    /**
     * @var MangopayHelper
     */
    private $mangopayHelper;

    /**
     * @var WalletHelper
     */
    private $walletHelper;

    private $entityManager;

    /**
     * PaymentOutHelper constructor.
     * @param MangopayHelper $mangopayHelper
     * @param EntityManager $entityManager
     * @param WalletHelper $walletHelper
     */
    public function __construct(MangopayHelper $mangopayHelper, EntityManager $entityManager, WalletHelper $walletHelper)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->entityManager = $entityManager;
        $this->walletHelper = $walletHelper;
    }

    /**
     * @param TransactionInterface $transfer
     * @param bool $needPersist
     * @return Transfer
     */
    public function createTransfer(TransactionInterface $transfer, $needPersist = true)
    {
        $debitedWallet = $transfer->getDebitedWallet();
        $creditedWallet = $transfer->getCreditedWallet();
        $currency = $debitedWallet->getCurrencyCode();

        $debitedFunds = $this->buildMoney($transfer->getDebitedFunds(), $currency);
        $fees = $this->buildMoney($transfer->getFees(), $currency);

        $mangoTransfer = new Transfer();
        $user = $debitedWallet->getUser();
        $mangoTransfer->AuthorId = $user->getMangoUserId();
        $mangoTransfer->DebitedFunds = $debitedFunds;
        $mangoTransfer->Fees = $fees;
        $mangoTransfer->DebitedWalletId = $debitedWallet->getMangoWalletId();
        $mangoTransfer->CreditedWalletId = $creditedWallet->getMangoWalletId();
        $mangoTransfer->Tag = $transfer->getTag();

        $mangoTransfer =  $this->mangopayHelper->Transfers->Create($mangoTransfer);

        $transfer->setMangoTransactionId($mangoTransfer->Id);
        $transfer->setStatus($mangoTransfer->Status);

        if($needPersist)
        {
            $this->entityManager->persist($transfer);
            $this->entityManager->flush();
        }

        return $mangoTransfer;
    }

    /**
     * @param string $amount
     * @param string $currency
     * @return Money
     */
    public function buildMoney($amount = '0', $currency = 'EUR')
    {
        $money = new Money();
        $money->Currency = $currency;
        $money->Amount = $amount;

        return $money;
    }
}
