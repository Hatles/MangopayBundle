<?php

namespace Troopers\MangopayBundle\Entity;

/**
 * Defines mandatory methods a Transaction need to be used in Mango
 * https://docs.mangopay.com/api-references/users/natural-users/.
 */
interface TransactionInterface
{
    const TYPE_PAYIN = "PAY_IN";
    const TYPE_PAYOUT = "PAYOUT";
    const TYPE_TRANSFER = "TRANSFER";
//    const TYPE_REFUND = "REFUND";

    const STATUS_CREATED = "CREATED";
    const STATUS_SUCCEEDED = "SUCCEEDED";
    const STATUS_FAILED = "FAILED";

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getMangoTransactionId();

    /**
     * @param  int
     */
    public function setMangoTransactionId($transactionId);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string
     */
    public function setType($type);

    /**
     * Author
     *
     * @return UserInterface
     */
    public function getAuthor();

    /**
     * It represents the amount debited on the bank account of the Author. In cents so 100€ will be written like « Amount » : 10000
     * DebitedFunds – Fees = CreditedFunds (amount received on wallet).
     *
     * @return int
     */
    public function getDebitedFunds();

    /**
     * It represents your fees taken on the DebitedFunds.In cents so 100€ will be written like « Amount » : 10000.
     *
     * @return int
     */
    public function getFees();

    /**
     * The credited wallet.
     *
     * @return  WalletInterface
     */
    public function getCreditedWallet();

    /**
     * URL Format expected.
     *
     * @return int
     */
    public function getCardType();

    /**
     * TransactionStatus {CREATED, SUCCEEDED, FAILED}
     * @return string
     */
    public function getStatus();


    /**
     * @param string
     */
    public function setStatus($status);
}
