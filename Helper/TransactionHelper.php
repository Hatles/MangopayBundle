<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 19/07/2017
 * Time: 19:32
 */

namespace Troopers\MangopayBundle\Helper;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Troopers\MangopayBundle\Entity\TransactionEntityInterface;
use Troopers\MangopayBundle\Entity\TransactionInterface;
use Troopers\MangopayBundle\Entity\TransactionType;

class TransactionHelper
{
    /**
     * @var MangopayHelper
     */
    private $mangopayHelper;

    /**
     * TransactionHelper constructor.
     * @param MangopayHelper $mangopayHelper
     */
    public function __construct(MangopayHelper $mangopayHelper)
    {
        $this->mangopayHelper = $mangopayHelper;
    }

    public function findTransaction(TransactionInterface $transaction)
    {
        $mangoTransaction = null;

        if ($mangoUserId = $transaction->getMangoTransactionId() && $type = $transaction->getType()) {
            switch ($type) {
                case TransactionInterface::TYPE_PAYIN:
                    $mangoTransaction = $this->mangopayHelper->PayIns->Get($mangoUserId);
                    break;
                case TransactionInterface::TYPE_PAYOUT:
                    $mangoTransaction = $this->mangopayHelper->PayOuts->Get($mangoUserId);
                    break;
                case TransactionInterface::TYPE_TRANSFER:
                    $mangoTransaction = $this->mangopayHelper->Transfers->Get($mangoUserId);
                    break;
                default:
                    throw new NotFoundHttpException("unknown transaction type :" . $type);
            };
        }

        return $mangoTransaction;
    }
}
