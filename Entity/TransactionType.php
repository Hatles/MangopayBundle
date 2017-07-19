<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 19/07/2017
 * Time: 19:42
 */

namespace Troopers\MangopayBundle\Entity;


abstract class TransactionType
{
    const PAYIN = "PAY_IN";
    const PAYOUT = "PAYOUT";
    const TRANSFER = "TRANSFER";
//    const REFUND = "REFUND";
}