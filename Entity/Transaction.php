<?php

namespace Troopers\MangopayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Troopers\MangopayBundle\Annotation\MangoPayField;

/**
 * Transaction.
 *
 * @ORM\MappedSuperclass
 */
abstract class Transaction implements TransactionInterface
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="mango_transaction_id", type="integer", nullable=true)
     */
    protected $mangoTransactionId;

    /**
     * Debited funds.
     *
     * @var int
     * @ORM\Column(name="debitedFunds", type="integer")
     */
    protected $debitedFunds;

    /**
     * Credited funds.
     *
     * @var int
     * @MangoPayField(dataTransformer="money")
     */
    protected $creditedFunds;

    /**
     * Fees.
     *
     * @var int
     * @ORM\Column(name="fees", type="integer")
     */
    protected $fees;

    /**
     * TransactionStatus {CREATED, SUCCEEDED, FAILED}.
     *
     * @var string
     * @ORM\Column(name="status", type="string", length=255)
     * @Assert\Choice(callback = "getStatuses")
     * @MangoPayField()
     */
    protected $status;

    /**
     * TransactionType {PAYIN, PAYOUT, TRANSFER}.
     *
     * @var string
     * @ORM\Column(name="type", type="string", length=255)
     * @Assert\Choice(callback = "getTypes")
     */
    protected $type;

    /**
     * Mangopay load fields
     */

    /**
     * Result code.
     *
     * @var string
     */
    protected $resultCode;

    /**
     * Result code.
     *
     * @var string
     */
    protected $tag;

    /**
     * The PreAuthorization result Message explaining the result code.
     *
     * @var string
     */
    protected $resultMessage;

    /**
     * Execution date;.
     *
     * @var \DateTime
     */
    protected $executionDate;

    /**
     * Creation date;.
     *
     * @var \DateTime
     * @MangoPayField(dataTransformer="date")
     */
    protected $creationDate;

    /**
     * TransactionNature { REGULAR, REFUND, REPUDIATION }.
     *
     * @var string
     */
    protected $nature;

    /**
     * PaymentType { CARD, DIRECT_DEBIT, PREAUTHORIZED, BANK_WIRE }.
     *
     * @var string
     */
    protected $paymentType;

    public function __construct()
    {
        $this->status = self::STATUS_PRE_CREATED;
        $this->fees = 0;
    }

    public static function getStatuses()
    {
        return array(self::STATUS_CREATED, self::STATUS_SUCCEEDED, self::STATUS_FAILED, self::STATUS_PRE_CREATED);
    }

    public static function getTypes()
    {
        return array(self::TYPE_PAYIN, self::TYPE_PAYOUT);
    }

    /**
     * @return int
     */
    public function getMangoTransactionId()
    {
        return $this->mangoTransactionId;
    }

    /**
     * @param int $mangoTransactionId
     */
    public function setMangoTransactionId($mangoTransactionId)
    {
        $this->mangoTransactionId = $mangoTransactionId;
    }

    /**
     * @return int
     */
    public function getDebitedFunds()
    {
        return $this->debitedFunds;
    }

    /**
     * @param int $debitedFunds
     */
    public function setDebitedFunds($debitedFunds)
    {
        $this->debitedFunds = $debitedFunds;
    }

    /**
     * @return int
     */
    public function getCreditedFunds()
    {
        return $this->creditedFunds;
    }

    /**
     * @param int $creditedFunds
     */
    public function setCreditedFunds($creditedFunds)
    {
        $this->creditedFunds = $creditedFunds;
    }

    /**
     * @return int
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * @param int $fees
     */
    public function setFees($fees)
    {
        $this->fees = $fees;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function setPayin()
    {
        $this->type = self::TYPE_PAYIN;
    }

    public function setPayout()
    {
        $this->type = self::TYPE_PAYOUT;
    }

    /**
     * @return string
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * @param string $resultCode
     */
    public function setResultCode($resultCode)
    {
        $this->resultCode = $resultCode;
    }

    /**
     * @return string
     */
    public function getResultMessage()
    {
        return $this->resultMessage;
    }

    /**
     * @param string $resultMessage
     */
    public function setResultMessage($resultMessage)
    {
        $this->resultMessage = $resultMessage;
    }

    /**
     * @return \DateTime
     */
    public function getExecutionDate()
    {
        return $this->executionDate;
    }

    /**
     * @param \DateTime $executionDate
     */
    public function setExecutionDate($executionDate)
    {
        $this->executionDate = $executionDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return string
     */
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * @param string $nature
     */
    public function setNature($nature)
    {
        $this->nature = $nature;
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     * @param string $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return BankInformationInterface
     */
    public function getCreditedAccount()
    {}
}
