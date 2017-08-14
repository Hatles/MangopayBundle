<?php

namespace Troopers\MangopayBundle\Helper;

use Doctrine\ORM\EntityManager;
use MangoPay\Money;
use MangoPay\PayOut;
use MangoPay\PayOutPaymentDetailsBankWire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Troopers\MangopayBundle\Entity\BankInformationInterface;
use Troopers\MangopayBundle\Entity\TransactionInterface;
use Troopers\MangopayBundle\Entity\WalletInterface;
use Troopers\MangopayBundle\Event\TransactionEvent;
use Troopers\MangopayBundle\TroopersMangopayEvents;

/**
 * ref: troopers_mangopay.payment_out_helper.
 **/
class PaymentOutHelper
{
    /**
     * @var MangopayHelper
     */
    private $mangopayHelper;

    /**
     * @var WalletHelper
     */
    private $walletHelper;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * PaymentOutHelper constructor.
     * @param MangopayHelper $mangopayHelper
     * @param WalletHelper $walletHelper
     * @param EntityManager $entityManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(MangopayHelper $mangopayHelper, WalletHelper $walletHelper, EntityManager $entityManager, EventDispatcherInterface $dispatcher)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->walletHelper = $walletHelper;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param BankInformationInterface $bankInformation
     * @param $currency
     * @param $debitedFunds
     * @param string $fees
     * @return PayOut
     */
    public function createPayOutForUserWithCurrency(BankInformationInterface $bankInformation, $currency, $debitedFunds, $fees = '0')
    {
        $debitedFunds = $this->buildMoney($debitedFunds, $currency);
        $fees = $this->buildMoney($fees, $currency);
        $meanOfPaymentDetails = $this->buildPayOutPaymentDetailsBankWire($bankInformation);

        $payOut = new PayOut();
        $user = $bankInformation->getUser();
        $payOut->AuthorId = $user->getMangoUserId();
        $payOut->DebitedWalletId = $this->walletHelper->findOrCreateWalletWithCurrency($user, $currency)->Id;
        $payOut->PaymentType = 'BANK_WIRE';
        $payOut->DebitedFunds = $debitedFunds;
        $payOut->MeanOfPaymentDetails = $meanOfPaymentDetails;
        $payOut->Fees = $fees;

        return $this->mangopayHelper->PayOuts->Create($payOut);
    }

    /**
     * @param BankInformationInterface $bankInformation
     * @param WalletInterface $wallet
     * @param $debitedFunds
     * @param string $fees
     * @return PayOut
     */
    public function createPayOutForUser(BankInformationInterface $bankInformation, WalletInterface $wallet, $debitedFunds, $fees = '0', $tag = '')
    {
        $currency = $wallet->getCurrencyCode();

        $debitedFunds = $this->buildMoney($debitedFunds, $currency);
        $fees = $this->buildMoney($fees, $currency);
        $meanOfPaymentDetails = $this->buildPayOutPaymentDetailsBankWire($bankInformation);

        $payOut = new PayOut();
        $user = $wallet->getUser();
        $payOut->AuthorId = $user->getMangoUserId();
        $payOut->DebitedWalletId = $wallet->getMangoWalletId();
        $payOut->PaymentType = 'BANK_WIRE';
        $payOut->DebitedFunds = $debitedFunds;
        $payOut->MeanOfPaymentDetails = $meanOfPaymentDetails;
        $payOut->Fees = $fees;
        $payOut->Tag = $tag;

        return $this->mangopayHelper->PayOuts->Create($payOut);
    }

    public function createPayOut(TransactionInterface $transaction)
    {
        $event = new TransactionEvent($transaction);
        $this->dispatcher->dispatch(TroopersMangopayEvents::PRE_CREATE_TRANSACTION, $event);

        $payOut = $this->createPayOutForUser(
            $transaction->getCreditedAccount(),
            $transaction->getDebitedWallet(),
            $transaction->getDebitedFunds(),
            $transaction->getFees(),
            $transaction->getTag()
        );

        $transaction->setMangoTransactionId($payOut->Id);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $payOut;
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

    /**
     * @param BankInformationInterface $bankInformation
     * @return PayOutPaymentDetailsBankWire
     */
    public function buildPayOutPaymentDetailsBankWire(BankInformationInterface $bankInformation)
    {
        $meanOfPaymentDetails = new PayOutPaymentDetailsBankWire();
        if (null === ($bankAccountId = $bankInformation->getMangoBankAccountId())) {
            throw new NotFoundHttpException(sprintf('bankAccount not found for bankInfo of user\'s id : %s', $bankInformation->getUser()->getId()));
        }
        $meanOfPaymentDetails->BankAccountId = $bankAccountId;

        return $meanOfPaymentDetails;
    }
}
