<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 06/07/2017
 * Time: 13:04
 */

namespace Troopers\MangopayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Troopers\MangopayBundle\Annotation\MangoPayField;

/**
 * KYC Document.
 *
 * @ORM\MappedSuperclass
 */
abstract class KycDocument implements KycDocumentInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="mango_kyc_document_id", type="integer", nullable=true)
     */
    protected $kycDocumentId;

    /**
     * @var string
     * @Assert\NotBlank(message="This document type is invalid")
     * @Assert\Choice(callback="getTypes")
     * @MangoPayField()
     */
    protected $type;

    /**
     * @var \DateTime
     * @MangoPayField(dataTransformer="date")
     */
    protected $creationDate;

    public function getDate($timeStamp)
    {
        $dtStr = date("c", $timeStamp);
        return new \DateTime($dtStr);
    }

    /**
     * @var string
     * @MangoPayField()
     */
    protected $status;

    /**
     * @var string
     * @MangoPayField()
     */
    protected $refusedReasonMessage;

    /**
     * @var string
     * @MangoPayField()
     */
    protected $refusedReasonType;

    public static function getTypes()
    {
        return array(self::TYPE_ADDRESS_PROOF, self::TYPE_ARTICLES_OF_ASSOCIATION, self::TYPE_IDENTITY_PROOF, self::TYPE_REGISTRATION_PROOF, self::TYPE_SHAREHOLDER_DECLARATION);
    }

    /**
     * @return int
     */
    public function getKycDocumentId()
    {
        return $this->kycDocumentId;
    }

    /**
     * @param int $kycDocumentId
     */
    public function setKycDocumentId($kycDocumentId)
    {
        $this->kycDocumentId = $kycDocumentId;
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
    public function getRefusedReasonMessage()
    {
        return $this->refusedReasonMessage;
    }

    /**
     * @param string $refusedReasonMessage
     */
    public function setRefusedReasonMessage($refusedReasonMessage)
    {
        $this->refusedReasonMessage = $refusedReasonMessage;
    }

    /**
     * @return string
     */
    public function getRefusedReasonType()
    {
        return $this->refusedReasonType;
    }

    /**
     * @param string $refusedReasonType
     */
    public function setRefusedReasonType($refusedReasonType)
    {
        $this->refusedReasonType = $refusedReasonType;
    }

    public function __toString()
    {
        return $this->getType() ?: "TYPE_NULL";
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
}
