<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 06/07/2017
 * Time: 10:47
 */

namespace Troopers\MangopayBundle\Helper;


use Doctrine\ORM\EntityManager;
use MangoPay\KycDocument;
use MangoPay\KycPage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Troopers\MangopayBundle\Entity\KycDocumentInterface;
use Troopers\MangopayBundle\Entity\KycPageInterface;
use Troopers\MangopayBundle\Entity\UserNaturalInterface;
use Troopers\MangopayBundle\Event\KycEvent;
use Troopers\MangopayBundle\TroopersMangopayEvents;

class KycHelper
{
    private $mangopayHelper;
    private $entityManager;
    private $dispatcher;
    private $userHelper;


    public function __construct(MangopayHelper $mangopayHelper, UserHelper $userHelper, EntityManager $entityManager, EventDispatcherInterface $dispatcher)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->userHelper = $userHelper;
    }

    public function findKycDocument(KycDocumentInterface $kycDocument)
    {
        $mangoDocument = null;

        if ($documentId = $kycDocument->getKycDocumentId()) {
            $mangoUser = $this->userHelper->findOrCreateMangoUser($kycDocument->getUser());
            $mangoDocument = $this->mangopayHelper->Users->GetKycDocument($mangoUser->Id, $documentId);
        }

        return $mangoDocument;
    }

    public function createKycDocumentForUser(UserNaturalInterface $user, $type)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($user);

        $kycDocument = new KycDocument();
        $kycDocument->Type = $type;

        $kycDocument = $this->mangopayHelper->Users->CreateKycDocument($mangoUser->Id, $kycDocument);

        $event = new KycEvent($kycDocument, $user, null);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_KYCDOCUMENT_FOR_USER, $event);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $kycDocument;
    }

    public function createKycPage(KycDocumentInterface $kycDocument, KycPageInterface $kycPage)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($kycDocument->getUser());
        $mangoDocument = $this->findOrCreateKycDocument($kycDocument);

        $mangoPage = new KycPage();
        $mangoPage->File = $kycPage->getFileBase64();

        $this->mangopayHelper->Users->CreateKycPage($mangoUser->Id, $mangoDocument->Id, $mangoPage);

        $event = new KycEvent($mangoDocument, $kycDocument->getUser(), $kycDocument);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_KYCPAGE, $event);

        $this->entityManager->persist($kycDocument);
        $this->entityManager->flush();

        return $mangoDocument;
    }

    public function findOrCreateKycDocument(KycDocumentInterface $kycDocument)
    {
        if ($documentId = $kycDocument->getKycDocumentId()) {
            $mangoUser = $this->userHelper->findOrCreateMangoUser($kycDocument->getUser());
            $mangoDocument = $this->mangopayHelper->Users->GetKycDocument($mangoUser->Id, $documentId);
        } else {
            $mangoDocument = $this->createKycDocument($kycDocument);
        }

        return $mangoDocument;
    }

    public function createKycDocument(KycDocumentInterface $kycDocument)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($kycDocument->getUser());
        $mangoDocument = new KycDocument();
        $mangoDocument->Type = $kycDocument->getType();

        $mangoDocument = $this->mangopayHelper->Users->CreateKycDocument($mangoUser->Id, $mangoDocument);

        $kycDocument->setKycDocumentId($mangoDocument->Id);

        $event = new KycEvent($mangoDocument, $kycDocument->getUser(), $kycDocument);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_KYCDOCUMENT, $event);

        $this->entityManager->persist($kycDocument);
        $this->entityManager->flush();

        return $mangoDocument;
    }

    public function submitKycDocument(KycDocumentInterface $kycDocument)
    {
        $mangoUser = $this->userHelper->findOrCreateMangoUser($kycDocument->getUser());
        $mangoDocument = $this->findOrCreateKycDocument($kycDocument);
        $mangoDocument->Status = "VALIDATION_ASKED";

        $mangoDocument = $this->mangopayHelper->Users->UpdateKycDocument($mangoUser->Id, $mangoDocument);

        $event = new KycEvent($mangoDocument, $kycDocument->getUser(), $kycDocument);
        $this->dispatcher->dispatch(TroopersMangopayEvents::ASK_VALIDATION_KYCDOCUMENT, $event);

        $this->entityManager->persist($kycDocument);
        $this->entityManager->flush();

        return $mangoDocument;
    }

    public function sendKycDocument(KycDocumentInterface $kycDocument)
    {
        $mangoDocument = $this->findOrCreateKycDocument($kycDocument);
        $mangoUser = $this->userHelper->findOrCreateMangoUser($kycDocument->getUser());

        foreach ($kycDocument->getPages() as $page) {
            /**
             * @var KycPageInterface $page
             */
            $mangoPage = new KycPage();
            $mangoPage->File = $page->getFileBase64();

            $this->mangopayHelper->Users->CreateKycPage($mangoUser->Id, $mangoDocument->Id, $mangoPage);
        }

        $mangoDocument->Status = "VALIDATION_ASKED";
        $mangoDocument = $this->mangopayHelper->Users->UpdateKycDocument($mangoUser->Id, $mangoDocument);

        $event = new KycEvent($mangoDocument, $kycDocument->getUser(), $kycDocument);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_KYCDOCUMENT, $event);

        $this->entityManager->persist($kycDocument);
        $this->entityManager->flush();

        return $mangoDocument;
    }
}
