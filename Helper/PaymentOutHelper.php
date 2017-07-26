<?php

namespace Troopers\MangopayBundle\Helper;

use MangoPay\Money;
use MangoPay\PayOut;
use MangoPay\PayOutPaymentDetailsBankWire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Troopers\MangopayBundle\Entity\BankInformationInterface;

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
     * PaymentOutHelper constructor.
     * @param MangopayHelper $mangopayHelper
     * @param WalletHelper $walletHelper
     */
    public function __construct(MangopayHelper $mangopayHelper, WalletHelper $walletHelper)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->walletHelper = $walletHelper;
    }

    /**
     * @param BankInformationInterface $bankInformation
     * @param $currency
     * @param $debitedFunds
     * @param string $fees
     * @return PayOut
     */
    public function createPayOutForUser(BankInformationInterface $bankInformation, $currency, $debitedFunds, $fees = '0')
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
