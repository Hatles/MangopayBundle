<?php

namespace Troopers\MangopayBundle\Helper;

use Doctrine\ORM\EntityManager;
use MangoPay\Address;
use MangoPay\UserLegal;
use MangoPay\UserNatural;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Troopers\MangopayBundle\Entity\AddressInterface;
use Troopers\MangopayBundle\Entity\CountryInterface;
use Troopers\MangopayBundle\Entity\UserLegalInterface;
use Troopers\MangopayBundle\Event\UserEvent;
use Troopers\MangopayBundle\TroopersMangopayEvents;

/**
 * ref: troopers_mangopay.user_helper.
 **/
class UserLegalHelper
{
    private $mangopayHelper;
    private $entityManager;
    private $dispatcher;

    public function __construct(MangopayHelper $mangopayHelper, EntityManager $entityManager, EventDispatcherInterface $dispatcher)
    {
        $this->mangopayHelper = $mangopayHelper;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param UserLegalInterface $user
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function findOrCreateMangoUserLegal(UserLegalInterface $user, $inLiveCycleCallback = false)
    {
        $mangoUser = null;

        if ($mangoUserId = $user->getMangoUserId()) {
            $mangoUser = $this->mangopayHelper->Users->GetLegal($mangoUserId);
        } elseif (!$inLiveCycleCallback) {
            $mangoUser = $this->createMangoUserLegal($user, $inLiveCycleCallback);
        }

        return $mangoUser;
    }

    /**
     * @param UserLegalInterface $user
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function createMangoUserLegal(UserLegalInterface $user, $inLiveCycleCallback = false)
    {
        $mangoUser = new UserLegal();
        $mangoUser->LegalPersonType = $user->getLegalPersonType();

        $birthday = null;
        if (($birthdayTmp = $user->getLegalRepresentativeBirthday()) instanceof \Datetime) {
            $birthday = $birthdayTmp->getTimestamp();
        }

        $nationality = null;
        if (($nationality = $user->getLegalRepresentativeNationality()) instanceof CountryInterface) {
            $nationality = $nationality->getCountryCode();
        }

        $countryOfResidence = null;
        if (($countryOfResidence = $user->getLegalRepresentativeCountryOfResidence()) instanceof CountryInterface) {
            $countryOfResidence = $countryOfResidence->getCountryCode();
        }

        //LIGHT
        //Business Name -
        $mangoUser->Name = $user->getName();
        //Generic business email -
        $mangoUser->Email = $user->getEmail();
        //First name of the legal representative -
        $mangoUser->LegalRepresentativeFirstName = $user->getLegalRepresentativeFirstName();
        //Last name of the legal representative -
        $mangoUser->LegalRepresentativeLastName = $user->getLegalRepresentativeLastName();
        //Birthday of the legal representative -
        $mangoUser->LegalRepresentativeBirthday = $birthday;
        //Nationality of the legal representative -
        $mangoUser->LegalRepresentativeNationality = $nationality;
        //Country of residence of the legal representative -
        $mangoUser->LegalRepresentativeCountryOfResidence = $countryOfResidence;

        $mangoUser->Tag = 'L_'.$user->getId();


        $headquartersAddress = null;
        if (($headquartersAddressTmp = $user->getHeadquartersAddress()) instanceof Address) {
            $headquartersAddress = $headquartersAddressTmp;
        } elseif ($headquartersAddressTmp instanceof AddressInterface) {
            $headquartersAddress = $headquartersAddressTmp->getMangoAddress();
        }

        $legalRepresentativeAddress = null;
        if (($legalRepresentativeAddressTmp = $user->getLegalRepresentativeAddress()) instanceof Address) {
            $legalRepresentativeAddress = $legalRepresentativeAddressTmp;
        } elseif ($legalRepresentativeAddressTmp instanceof AddressInterface) {
            $legalRepresentativeAddress = $legalRepresentativeAddressTmp->getMangoAddress();
        }

        //REGULAR
        //Headquarters address -
        $mangoUser->HeadquartersAddress = $headquartersAddress;
        //Legal representative email -
        $mangoUser->LegalRepresentativeEmail = $user->getLegalRepresentativeEmail();
        //Legal representative address -
        $mangoUser->LegalRepresentativeAddress = $legalRepresentativeAddress;


        $mangoUser = $this->mangopayHelper->Users->Create($mangoUser);

        $user->setMangoUserId($mangoUser->Id);

        $event = new UserEvent($user, $mangoUser);
        $this->dispatcher->dispatch(TroopersMangopayEvents::NEW_USER_LEGAL, $event);

        if (!$inLiveCycleCallback) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $mangoUser;
    }

    /**
     * @param UserLegalInterface $user
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function updateOrPersistMangoUserLegal(UserLegalInterface $user)
    {
        if (!$user->getId() || !$user->getMangoUserId()) {
            return $this->createMangoUserLegal($user, true);
        } else {
            return $this->updateMangoUserLegal($user, true);
        }
    }

    /**
     * @param UserLegalInterface $user
     * @param bool $inLiveCycleCallback
     * @return \MangoPay\UserLegal|UserNatural
     */
    public function updateMangoUserLegal(UserLegalInterface $user, $inLiveCycleCallback = false)
    {
        $mangoUserId = $user->getMangoUserId();
        $mangoUser = $this->mangopayHelper->Users->GetLegal($mangoUserId);

        $birthday = null;
        if (($birthdayTmp = $user->getLegalRepresentativeBirthday()) instanceof \Datetime) {
            $birthday = $birthdayTmp->getTimestamp();
        }

        $nationality = null;
        if (($nationality = $user->getLegalRepresentativeNationality()) instanceof CountryInterface) {
            $nationality = $nationality->getCountryCode();
        }

        $countryOfResidence = null;
        if (($countryOfResidence = $user->getLegalRepresentativeCountryOfResidence()) instanceof CountryInterface) {
            $countryOfResidence = $countryOfResidence->getCountryCode();
        }

        //LIGHT
        //Business Name -
        $mangoUser->Name = $user->getName();
        //Generic business email -
        $mangoUser->Email = $user->getEmail();
        //First name of the legal representative -
        $mangoUser->LegalRepresentativeFirstName = $user->getLegalRepresentativeFirstName();
        //Last name of the legal representative -
        $mangoUser->LegalRepresentativeLastName = $user->getLegalRepresentativeLastName();
        //Birthday of the legal representative -
        $mangoUser->LegalRepresentativeBirthday = $birthday;
        //Nationality of the legal representative -
        $mangoUser->LegalRepresentativeNationality = $nationality;
        //Country of residence of the legal representative -
        $mangoUser->LegalRepresentativeCountryOfResidence = $countryOfResidence;

        $mangoUser->Tag = 'L_'.$user->getId();


        $headquartersAddress = null;
        if (($headquartersAddressTmp = $user->getHeadquartersAddress()) instanceof Address) {
            $headquartersAddress = $headquartersAddressTmp;
        } elseif ($headquartersAddressTmp instanceof AddressInterface) {
            $headquartersAddress = $headquartersAddressTmp->getMangoAddress();
        }

        $legalRepresentativeAddress = null;
        if (($legalRepresentativeAddressTmp = $user->getLegalRepresentativeAddress()) instanceof Address) {
            $legalRepresentativeAddress = $legalRepresentativeAddressTmp;
        } elseif ($legalRepresentativeAddressTmp instanceof AddressInterface) {
            $legalRepresentativeAddress = $legalRepresentativeAddressTmp->getMangoAddress();
        }

        //REGULAR
        //Headquarters address -
        $mangoUser->HeadquartersAddress = $headquartersAddress;
        //Legal representative email -
        $mangoUser->LegalRepresentativeEmail = $user->getLegalRepresentativeEmail();
        //Legal representative address -
        $mangoUser->LegalRepresentativeAddress = $legalRepresentativeAddress;


        $mangoUser = $this->mangopayHelper->Users->Update($mangoUser);

//        $event = new UserEvent($user, $mangoUser);
//        $this->dispatcher->dispatch(TroopersMangopayEvents::UPDATE_USER_LEGAL, $event);

        if (!$inLiveCycleCallback) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $mangoUser;
    }
}
