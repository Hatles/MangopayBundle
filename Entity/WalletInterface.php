<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 04/07/2017
 * Time: 13:39
 */

namespace Troopers\MangopayBundle\Entity;


interface WalletInterface
{
    /**
     * @return UserNaturalInterface
     */
    public function getUser();

    /**
     * @param UserNaturalInterface $user
     */
    public function setUser($user);

    /**
     * @return int
     */
    public function getMangoWalletId();

    /**
     * @param int $walletId
     */
    public function setMangoWalletId($walletId);

    /**
     * @return string
     *             Wallets’s currency. Should be ISO_4217 format
     */
    public function getCurrencyCode();

    /**
     * @return string
     */
    public function getDescription();
}
