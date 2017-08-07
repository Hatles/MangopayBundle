<?php

namespace Troopers\MangopayBundle\Helper;

use MangoPay\CardRegistration;
use MangoPay\Money;
use MangoPay\PayIn;
use MangoPay\PayInExecutionDetailsDirect;
use MangoPay\PayInPaymentDetailsCard;
use MangoPay\User;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Troopers\MangopayBundle\Entity\TransactionInterface;
use Troopers\MangopayBundle\Entity\UserNaturalInterface;
use Troopers\MangopayBundle\Entity\WalletInterface;
use Troopers\MangopayBundle\Event\CardRegistrationEvent;
use Troopers\MangopayBundle\TroopersMangopayEvents;

/**
 * ref: troopers_mangopay.payment_direct_helper.
 **/
class PaymentDirectHelper
{
    private $mangopayHelper;
    private $walletHelper;
    private $router;
    private $dispatcher;
    private $container;

    public function __construct(ContainerInterface $container, MangopayHelper $mangopayHelper, WalletHelper $walletHelper, Router $router, EventDispatcherInterface $dispatcher)
    {
        $this->container = $container;
        $this->mangopayHelper = $mangopayHelper;
        $this->walletHelper = $walletHelper;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param UserNaturalInterface $user
     * @return PayInPaymentDetailsCard
     */
    public function buildPayInPaymentDetailsCard(UserNaturalInterface $user)
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
     * @param UserNaturalInterface $author
     * @param UserNaturalInterface $userCredited
     * @param string $currency
     * @param int $amount
     * @param int $fees
     * @return TransactionInterface
     */
    public function buildTransactionWithCurrency(UserNaturalInterface $author, UserNaturalInterface $userCredited, $currency, $amount, $fees = 0)
    {
        $class = $this->container->getParameter('troopers_mangopay.transaction.class');

        /**
         * @var TransactionInterface $transaction
         */
        $transaction = new $class();
        $transaction->setAuthor($author->getMangoUserId());
        $transaction->setDebitedFunds($amount);
        $transaction->setFees($fees);
        $transaction->setCreditedWallet($this->walletHelper->findOrCreateWalletWithCurrency($userCredited, $currency));

        return $transaction;
    }

    /**
     * @param UserNaturalInterface $author
     * @param WalletInterface $wallet
     * @param CardRegistration $cardRegistration
     * @param $amount
     * @param int $fees
     * @param null|string $secureModeReturnURL
     * @param null|string $payInTag
     * @return PayIn
     */
    public function executeTransaction(
        UserInterface $author,
        WalletInterface $wallet,
        CardRegistration $cardRegistration,
        $amount,
        $fees = 0,
        $secureModeReturnURL = null,
        $payInTag = null
    )
    {
        $transaction = $this->buildTransaction($author, $wallet, $amount, $fees);

        $paymentDetails = new PayInPaymentDetailsCard();
        $paymentDetails->CardType = $cardRegistration->CardType;
        $paymentDetails->CardId = $cardRegistration->CardId;

        $executionDetails = $this->buildPayInExecutionDetailsDirect($secureModeReturnURL);
        $mangoTransaction = $this->createDirectTransaction($transaction, $executionDetails, $paymentDetails, $payInTag);

        return $mangoTransaction;
    }

    /**
     * @param UserNaturalInterface $author
     * @param WalletInterface $wallet
     * @param int $amount
     * @param int $fees
     * @return TransactionInterface
     */
    public function buildTransaction(UserNaturalInterface $author, WalletInterface $wallet, $amount, $fees = 0)
    {
        $class = $this->container->getParameter('troopers_mangopay.transaction.class');

        /**
         * @var TransactionInterface $transaction
         */
        $transaction = new $class();
        $transaction->setAuthor($author->getMangoUserId());
        $transaction->setDebitedFunds($amount);
        $transaction->setFees($fees);
        $transaction->setCreditedWallet($wallet);

        return $transaction;
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

    public function createDirectTransaction(
        TransactionInterface $transaction,
        $executionDetails = null,
        $paymentDetails = null,
        $payInTag = null
    )
    {
        $currency = $transaction->getCreditedWallet()->getCurrencyCode();

        $debitedFunds = new Money();
        $debitedFunds->Currency = $currency;
        $debitedFunds->Amount = $transaction->getDebitedFunds();

        $fees = new Money();
        $fees->Currency = $currency;
        $fees->Amount = $transaction->getFees() ?: 0;

        $payIn = new PayIn();
        $payIn->PaymentType = 'DIRECT_DEBIT';
        $payIn->AuthorId = $transaction->getAuthor()->getMangoUserId();
        $payIn->CreditedWalletId = $transaction->getCreditedWallet()->getMangoWalletId();
        $payIn->DebitedFunds = $debitedFunds;
        $payIn->Fees = $fees;
        $payIn->Tag = $payInTag ?: $transaction->getTag();

        $payIn->Nature = 'REGULAR';
        $payIn->Type = 'PAYIN';
        $transaction->setType('PAYIN');

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

    /**
     * @param TransactionInterface $transaction
     * @param CardRegistration $cardRegistration
     * @param null $secureModeReturnURL
     * @param null $payInTag
     * @return PayIn
     */
    public function executeDirectTransaction(
        TransactionInterface $transaction,
        CardRegistration $cardRegistration,
        $secureModeReturnURL = null,
        $payInTag = null
    )
    {
        $paymentDetails = new PayInPaymentDetailsCard();
        $paymentDetails->CardType = $cardRegistration->CardType;
        $paymentDetails->CardId = $cardRegistration->CardId;

        $executionDetails = $this->buildPayInExecutionDetailsDirect($secureModeReturnURL);
        $mangoTransaction = $this->createDirectTransaction($transaction, $executionDetails, $paymentDetails, $payInTag);

        return $mangoTransaction;
    }

    public function prepareCardRegistrationCallback(User $user, TransactionInterface $transaction)
    {
        $cardRegistration = new CardRegistration();
        $cardRegistration->UserId = $user->Id;
        $cardRegistration->Currency = $transaction->getCreditedWallet()->getCurrencyCode();
        $mangoCardRegistration = $this->mangopayHelper->CardRegistrations->create($cardRegistration);

        $event = new CardRegistrationEvent($cardRegistration);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_CARD_REGISTRATION, $event);

        $cardRegistrationURL = $mangoCardRegistration->CardRegistrationURL;
        $preregistrationData = $mangoCardRegistration->PreregistrationData;
        $accessKey = $mangoCardRegistration->AccessKey;

        $redirect = $this->router->generate(
            'troopers_mangopaybundle_direct_payment_finalize',
            [
                'transaction' => $transaction->getId(),
                'cardId' => $mangoCardRegistration->Id,
            ]
        );

        $successRedirect = $this->generateSuccessUrl($transaction->getId());

        return [
            'callback' => 'payAjaxOrRedirect("'
                . $redirect . '", "'
                . $redirect . '", "'
                . $cardRegistrationURL . '", "'
                . $preregistrationData . '", "'
                . $accessKey . '", "'
                . $successRedirect . '")',
        ];
    }

    public function generateSuccessUrl($transactionId)
    {
        return $this->router->generate('troopers_mangopaybundle_direct_payment_success', ['transactionId' => $transactionId]);
    }

    /**
     * Update card registration with token.
     *
     * @param string $cardId
     * @param string $data
     * @param string $errorCode
     *
     * @return CardRegistration
     */
    public function updateCardRegistration($cardId, $data, $errorCode)
    {
        $cardRegister = $this->mangopayHelper->CardRegistrations->Get($cardId);
        $cardRegister->RegistrationData = $data ? 'data=' . $data : 'errorCode=' . $errorCode;

        $updatedCardRegister = $this->mangopayHelper->CardRegistrations->Update($cardRegister);

        $event = new CardRegistrationEvent($updatedCardRegister);
        $this->dispatcher->dispatch(TroopersMangopayEvents::UPDATE_CARD_REGISTRATION, $event);

        return $updatedCardRegister;
    }

}
