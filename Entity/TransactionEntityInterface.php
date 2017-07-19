<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 19/07/2017
 * Time: 19:30
 */

namespace Troopers\MangopayBundle\Entity;


interface TransactionEntityInterface
{
    /**
     * @return int
     */
    public function getMangoTransactionId();

    /**
     * @return string
     */
    public function getType();
}