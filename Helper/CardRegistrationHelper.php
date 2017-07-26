<?php

namespace Troopers\MangopayBundle\Helper;

use Doctrine\ORM\EntityManager;
use MangoPay\CardRegistration;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Troopers\MangopayBundle\Entity\UserNaturalInterface;

/**
 * ref: troopers_mangopay.card_registration_helper.
 **/
class CardRegistrationHelper
{
    private $mangopayHelper;

    public function __construct(MangopayHelper $mangopayHelper)
    {
        $this->mangopayHelper = $mangopayHelper;
    }

    public function createCardRegistrationForUser(UserNaturalInterface $user)
    {
        $cardRegistration = new CardRegistration();
        $cardRegistration->UserId = $user->getMangoUserId();
        $cardRegistration->Tag = 'user id : ' . $user->getId();
        $cardRegistration->Currency = 'EUR';

        $cardRegistration = $this->mangopayHelper->CardRegistrations->Create($cardRegistration);

        return $cardRegistration;
    }
}
