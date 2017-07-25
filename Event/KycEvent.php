<?php

namespace Troopers\MangopayBundle\Event;

use MangoPay\KycDocument;
use Symfony\Component\EventDispatcher\Event;
use Troopers\MangopayBundle\Entity\KycDocumentInterface;
use Troopers\MangopayBundle\Entity\UserInterface;
use Troopers\MangopayBundle\Entity\UserNaturalInterface;

class KycEvent extends Event
{
    /**
     * @var KycDocument
     */
    private $mangoDocument;

    /**
     * @var UserNaturalInterface
     */
    private $user;

    /**
     * @var KycDocumentInterface
     */
    private $kycDocument;

    /**
     * WalletEvent constructor.
     * @param KycDocument $mangoDocument
     * @param UserInterface $user
     * @param KycDocumentInterface $kycDocument
     */
    public function __construct(KycDocument $mangoDocument, UserInterface $user, KycDocumentInterface $kycDocument)
    {
        $this->mangoDocument = $mangoDocument;
        $this->user = $user;
        $this->kycDocument = $kycDocument;
    }

    /**
     * @return KycDocument
     */
    public function getMangoDocument()
    {
        return $this->mangoDocument;
    }

    /**
     * @param KycDocument $mangoDocument
     */
    public function setMangoDocument($mangoDocument)
    {
        $this->mangoDocument = $mangoDocument;
    }

    /**
     * @return UserNaturalInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserNaturalInterface $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return KycDocumentInterface
     */
    public function getKycDocument()
    {
        return $this->kycDocument;
    }

    /**
     * @param KycDocumentInterface $kycDocument
     */
    public function setKycDocument($kycDocument)
    {
        $this->kycDocument = $kycDocument;
    }
}
