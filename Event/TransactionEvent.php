<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 14/08/2017
 * Time: 15:46
 */

namespace Troopers\MangopayBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Troopers\MangopayBundle\Entity\Transaction;
use Troopers\MangopayBundle\Entity\TransactionInterface;

class TransactionEvent extends Event
{
    /**
     * @var TransactionInterface
     */
    private $transaction;

    /**
     * TransactionEvent constructor.
     * @param TransactionInterface $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * @return TransactionInterface
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param TransactionInterface $transaction
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
    }
}
