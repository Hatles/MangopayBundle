<?php

namespace Troopers\MangopayBundle\Entity;

use MangoPay\Address;

/**
 * Defines mandatory methods BankInformation need to be used in Mango
 * https://docs.mangopay.com/api-references/users/natural-users/.
 */
interface BankInformationInterface
{
    /**
     * @return UserInterface
     */
    public function getUser();

    /**
     * @return int
     */
    public function getMangoBankAccountId();

    /**
     * @param int $accountId
     */
    public function setMangoBankAccountId($accountId);

    /**
     * Author Mango Id.
     *
     * @return Address
     */
    public function getMangoAddress();

    /**
     *
     * @return string
     */
    public function getOwnerName();

    /**
     * It represents the amount debited on the bank account of the Author.In cents so 100€ will be written like « Amount » : 10000
     * DebitedFunds – Fees = CreditedFunds (amount received on wallet).
     *
     * @return string
     */
    public function getIban();
}
