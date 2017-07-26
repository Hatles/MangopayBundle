<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 24/07/2017
 * Time: 19:16
 */

namespace Troopers\MangopayBundle\Entity;


use MangoPay\Address;

interface AddressInterface
{
    /**
     * @return Address
     */
    public function getMangoAddress();
}
