<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 06/07/2017
 * Time: 13:04
 */

namespace Troopers\MangopayBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * KYC Document.
 *
 * @ORM\MappedSuperclass
 *
 */
//@MangoPayEntity(supportPersistAndUpdate=false)
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
     */
    protected $type;

    /**
     * @var Collection $pages
     * @Assert\NotBlank(message="A KYC document need at least one page.")
     * @Assert\Valid()
     */
    protected $pages;

    /**
     * KycDocument constructor.
     */
    public function __construct()
    {
        $this->pages = new ArrayCollection();
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

    public static function getTypes()
    {
        return array(self::TYPE_ADDRESS_PROOF, self::TYPE_ARTICLES_OF_ASSOCIATION, self::TYPE_IDENTITY_PROOF, self::TYPE_REGISTRATION_PROOF, self::TYPE_SHAREHOLDER_DECLARATION);
    }

    /**
     * @return Collection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param Collection $pages
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function validatePages(ExecutionContextInterface $context, $payload)
    {
        if ($this->getPages()->isEmpty()) {
            $context->buildViolation('A KYC document need at least one page.')
                ->atPath('pages')
                ->addViolation();
        }
    }
}