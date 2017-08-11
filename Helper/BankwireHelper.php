<?php

namespace Troopers\MangopayBundle\Helper;

use Doctrine\ORM\EntityManager;
use MangoPay\Money;
use MangoPay\PayIn;
use MangoPay\PayInExecutionDetailsDirect;
use MangoPay\PayInPaymentDetailsBankWire;
use MangoPay\Transaction;
use MangoPay\Wallet as MangoWallet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\DataCollectorTranslator;
use Troopers\MangopayBundle\Entity\TransactionInterface;
use Troopers\MangopayBundle\Entity\WalletInterface;
use Troopers\MangopayBundle\Event\PayInEvent;
use Troopers\MangopayBundle\Exception\MangopayPayInCreationException;
use Troopers\MangopayBundle\TroopersMangopayEvents;

/**
 * ref: troopers_mangopay.bankwire_helper.
 **/
class BankwireHelper
{
    protected $mangopayHelper;

    /**
     * @var UserHelper
     */
    protected $userHelper;

    /**
     * @var WalletHelper
     */
    protected $walletHelper;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var DataCollectorTranslator
     */
    protected $translator;

    private $entityManager;

    /**
     * BankwireHelper constructor.
     * @param $mangopayHelper
     * @param EntityManager $entityManager
     * @param UserHelper $userHelper
     * @param WalletHelper $walletHelper
     * @param EventDispatcherInterface $dispatcher
     * @param DataCollectorTranslator $translator
     */
    public function __construct($mangopayHelper, EntityManager $entityManager, UserHelper $userHelper, WalletHelper $walletHelper, EventDispatcherInterface $dispatcher, DataCollectorTranslator $translator)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->entityManager = $entityManager;
        $this->userHelper = $userHelper;
        $this->walletHelper = $walletHelper;
        $this->dispatcher = $dispatcher;
        $this->translator = $translator;
    }

    /**
     * Create a bankWire as discribed here: https://docs.mangopay.com/endpoints/v2/payins#e288_the-direct-debit-web-payin-object.
     *
     * @param MangoWallet $wallet
     * @param        $authorId
     * @param        $creditedUserId
     * @param        $amount
     * @param        $feesAmount
     *
     * @return PayIn
     */
    public function bankwireToMangoWallet(MangoWallet $wallet, $authorId, $creditedUserId, $amount, $feesAmount = 0)
    {
        $debitedFunds = new Money();
        $debitedFunds->Amount = $amount * 100;
        $debitedFunds->Currency = 'EUR';
        $fees = new Money();
        $fees->Amount = $feesAmount;
        $fees->Currency = 'EUR';
        $payin = new PayIn();
        $payin->CreditedWalletId = $wallet->Id;
        $payin->ExecutionType = 'Direct';
        $executionDetails = new PayInExecutionDetailsDirect();
        $payin->ExecutionDetails = $executionDetails;
        $paymentDetails = new PayInPaymentDetailsBankWire();
        $paymentDetails->DeclaredDebitedFunds = $debitedFunds;
        $paymentDetails->DeclaredFees = $fees;
        $payin->PaymentDetails = $paymentDetails;
        $payin->AuthorId = $authorId;
        $payin->CreditedUserId = $creditedUserId;

        $bankWire = $this->mangopayHelper->PayIns->Create($payin);

        return $bankWire;
    }

    /**
     * @param WalletInterface $wallet
     * @param $amount
     * @param int $feesAmount
     * @param string $tag
     * @param null $creditedUserId
     * @return Transaction
     * @throws MangopayPayInCreationException
     */
    public function bankwireToWallet(WalletInterface $wallet, $amount, $feesAmount = 0, $tag = '',  $creditedUserId = null)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($wallet->getUser());
        $mangoWallet = $this->walletHelper->findOrCreateWallet($wallet);

        $debitedFunds = new Money();
        $debitedFunds->Amount = $amount;
        $debitedFunds->Currency = $mangoWallet->Currency;
        $fees = new Money();
        $fees->Amount = $feesAmount;
        $fees->Currency = $mangoWallet->Currency;
        $payin = new PayIn();
        $payin->CreditedWalletId = $mangoWallet->Id;
        $payin->ExecutionType = 'Direct';
        $executionDetails = new PayInExecutionDetailsDirect();
        $payin->ExecutionDetails = $executionDetails;
        $paymentDetails = new PayInPaymentDetailsBankWire();
        $paymentDetails->DeclaredDebitedFunds = $debitedFunds;
        $paymentDetails->DeclaredFees = $fees;
        $payin->PaymentDetails = $paymentDetails;
        $payin->AuthorId = $mangoUser->Id;
        $payin->Tag = $tag;
        if ($creditedUserId !== null)
        {
            $payin->CreditedUserId = $creditedUserId;
        }

        $bankWire = $this->mangopayHelper->PayIns->Create($payin);

        if (property_exists($bankWire, 'Status') && $bankWire->Status != 'FAILED') {
            $event = new PayInEvent($payin);
            $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_BANK_WIRE_PAY_IN, $event);

            return $bankWire;
        }

        $event = new PayInEvent($bankWire);
        $this->dispatcher->dispatch(TroopersMangopayEvents::ERROR_BANK_WIRE_PAY_IN, $event);

        throw new MangopayPayInCreationException($this->translator->trans(
            'mangopay.error.' . $bankWire->ResultCode,
            [], 'messages'
        ));
    }

    public function createBankWirePayIn(TransactionInterface $transaction)
    {
        $bankWire = $this->bankwireToWallet(
            $transaction->getCreditedWallet(),
            $transaction->getDebitedFunds(),
            $transaction->getFees(),
            $transaction->getTag()
        );

        $transaction->setMangoTransactionId($bankWire->Id);
        $transaction->setStatus($bankWire->Status);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $bankWire;
    }
}
