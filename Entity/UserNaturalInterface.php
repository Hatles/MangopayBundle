<?php

namespace Troopers\MangopayBundle\Entity;

use Doctrine\Common\Collections\Collection;
use MangoPay\Address;

/**
 * Defines mandatory methods a Mango user should have
 * https://docs.mangopay.com/api-references/users/natural-users/.
 */
interface UserNaturalInterface extends UserInterface
{
    /**
     * @return Collection
     */
    public function getWallets();

    /**
     * @return int
     */
    public function getCardId();

    /**
     * @return Collection
     */
    public function getBankAccounts();

    /**
     * @return string
     *             User’s e-mail. A correct email address is expected
     */
    public function getEmail();

    /**
     * @return string
     *             User’s firstname (<100 chars)
     */
    public function getFirstname();

    /**
     * @return string
     *             User’s lastname (<100 chars)
     */
    public function getLastname();

    /**
     * @return string
     *             User’s fullname
     */
    public function getFullName();

    /**
     * @return \DateTime
     *           User’s birthday.
     */
    public function getBirthDay();

    /**
     * @return string
     *             User’s Nationality. ISO 3166-1 alpha-2 format is expected
     */
    public function getNationalityCode();

    /**
     * @return string
     *             User’s country of residence. ISO 3166-1 alpha-2 format is expected
     */
    public function getCountryCode();

    /**
     * @return Address
     */
    public function getMangoAddress();

    /**
     * @return string
     */
    public function getOccupation();

    /**
     * @return int
     * Could be only one of these values:
     * 1 - for incomes <18K€),
     * 2 - for incomes between 18 and 30K€,
     * 3 - for incomes between 30 and 50K€,
     * 4 - for incomes between 50 and 80K€,
     * 5 - for incomes between 80 and 120K€,
     * 6 - for incomes >120K€
     */
    public function getIncomeRange();
}
