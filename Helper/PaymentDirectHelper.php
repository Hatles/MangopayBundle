<?php

namespace Troopers\MangopayBundle\Helper;

use MangoPay\Money;
use MangoPay\PayIn;
use MangoPay\PayInExecutionDetailsDirect;
use MangoPay\PayInPaymentDetailsCard;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Troopers\MangopayBundle\Entity\Transaction;
use Troopers\MangopayBundle\Entity\TransactionInterface;
use Troopers\MangopayBundle\Entity\UserInterface;

/**
 * ref: troopers_mangopay.payment_direct_helper.
 **/
class PaymentDirectHelper
{
    private $mangopayHelper;
    private $walletHelper;
    private $router;
    private $dispatcher;

    public function __construct(MangopayHelper $mangopayHelper, WalletHelper $walletHelper, Router $router, EventDispatcherInterface $dispatcher)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->walletHelper = $walletHelper;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param UserInterface $user
     * @return PayInPaymentDetailsCard
     */
    public function buildPayInPaymentDetailsCard(UserInterface $user)
    {
        $paymentDetails = new PayInPaymentDetailsCard();
        $paymentDetails->CardType = 'CB_VISA_MASTERCARD';
        if (null === $cardId = $user->getCardId()) {
            throw new NotFoundHttpException(sprintf('CardId not found for user id : %s', $user->getId()));
        }
        $paymentDetails->CardId = $cardId;

        return $paymentDetails;
    }

    /**
     * @param $secureModeReturnURL
     * @return PayInExecutionDetailsDirect
     */
    public function buildPayInExecutionDetailsDirect($secureModeReturnURL)
    {
        $executionDetails = new PayInExecutionDetailsDirect();
        $executionDetails->SecureModeReturnURL = $secureModeReturnURL;

        return $executionDetails;
    }

    /**
     * @param UserInterface $userDebited
     * @param UserInterface $userCredited
     * @param $amount
     * @param $fees
     * @return Transaction
     */
    public function buildTransaction(UserInterface $userDebited, UserInterface $userCredited, $currency, $amount, $fees)
    {
        $transaction = new Transaction();
        $transaction->setAuthorId($userDebited->getMangoUserId());
        $transaction->setCreditedUserId($userCredited->getMangoUserId());
        $transaction->setDebitedFunds($amount);
        $transaction->setFees($fees);
        $transaction->setCreditedWalletId($this->walletHelper->findOrCreateWalletWithCurrency($userCredited, $currency)->Id);

        return $transaction;
    }

    /**
     * @param UserInterface $userDebited
     * @param UserInterface $userCredited
     * @param $amount
     * @param $fees
     * @param null $secureModeReturnURL
     * @param null $payInTag
     * @return PayIn
     */
    public function executeDirectTransaction(
        UserInterface $userDebited,
        UserInterface $userCredited,
        $amount,
        $fees,
        $secureModeReturnURL = null,
        $payInTag = null
    ) {
        $paymentDetails = $this->buildPayInPaymentDetailsCard($userDebited);
        $executionDetails = $this->buildPayInExecutionDetailsDirect($secureModeReturnURL);
        $transaction = $this->buildTransaction($userDebited, $userCredited, $amount, $fees);
        $mangoTransaction = $this->createDirectTransaction($transaction, $executionDetails, $paymentDetails, $payInTag);

        return $mangoTransaction;
    }

    public function createDirectTransaction(
        TransactionInterface $transaction,
        $executionDetails = null,
        $paymentDetails = null,
        $payInTag = null
    ) {
        $debitedFunds = new Money();
        $debitedFunds->Currency = 'EUR';
        $debitedFunds->Amount = $transaction->getDebitedFunds();

        $fees = new Money();
        $fees->Currency = 'EUR';
        $fees->Amount = $transaction->getFees();

        $payIn = new PayIn();
        $payIn->PaymentType = 'DIRECT_DEBIT';
        $payIn->AuthorId = $transaction->getAuthorId();
        $payIn->CreditedWalletId = $transaction->getCreditedWalletId();
        $payIn->DebitedFunds = $debitedFunds;
        $payIn->Fees = $fees;
        $payIn->Tag = $payInTag;

        $payIn->Nature = 'REGULAR';
        $payIn->Type = 'PAYIN';

        if (null === $paymentDetails) {
            $payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsCard();
            $payIn->PaymentDetails->CardType = 'CB_VISA_MASTERCARD';
        } elseif (!$paymentDetails instanceof \MangoPay\PayInPaymentDetailsCard) {
            throw new \Exception('unable to process PaymentDetails');
        } else {
            $payIn->PaymentDetails = $paymentDetails;
        }

        //@TODO : Find a better way to send default to this function to set default
        if (!$executionDetails instanceof \MangoPay\PayInExecutionDetails) {
            $payIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsWeb();
//            $payIn->ExecutionDetails->ReturnURL = 'https://www.example.com/bank';
//            $payIn->ExecutionDetails->TemplateURL = 'https://TemplateURL.com';
            $payIn->ExecutionDetails->SecureMode = 'DEFAULT';
            $payIn->ExecutionDetails->Culture = 'fr';
        } else {
            $payIn->ExecutionDetails = $executionDetails;
        }

        $mangoPayTransaction = $this->mangopayHelper->PayIns->create($payIn);

        //TODO
//        $event = new CardRegistrationEvent($cardRegistration);
//        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_CARD_REGISTRATION, $event);

        return $mangoPayTransaction;
    }
}
