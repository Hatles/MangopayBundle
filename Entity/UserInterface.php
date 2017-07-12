<?php

namespace Troopers\MangopayBundle\Entity;

use Doctrine\Common\Collections\Collection;
use MangoPay\Address;

/**
 * Defines mandatory methods a Mango user should have
 * https://docs.mangopay.com/api-references/users/natural-users/.
 */
interface UserInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getMangoUserId();

    /**
     * @param int $userId
     */
    public function setMangoUserId($userId);

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
}
