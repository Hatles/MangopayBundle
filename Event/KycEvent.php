<?php

namespace Troopers\MangopayBundle\Event;

use MangoPay\KycDocument;
use Symfony\Component\EventDispatcher\Event;
use Troopers\MangopayBundle\Entity\KycDocumentInterface;
use Troopers\MangopayBundle\Entity\UserInterface;

class KycEvent extends Event
{
    /**
     * @var KycDocument
     */
    private $mangoDocument;

    /**
     * @var UserInterface
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
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
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
