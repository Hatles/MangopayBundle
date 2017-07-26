<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 24/07/2017
 * Time: 17:10
 */

namespace Troopers\MangopayBundle\Entity;


use MangoPay\Address;

interface UserLegalInterface extends UserInterface
{
    const LEGAL_TYPE_BUSINESS = 'BUSINESS';
    const LEGAL_TYPE_ORGANIZATION = 'ORGANIZATION';
    const LEGAL_TYPE_SOLETRADER = 'SOLETRADER';

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLegalPersonType();

    /**
     * @return Address|AddressInterface
     */
    public function getHeadquartersAddress();

    /**
     * @return string
     */
    public function getLegalRepresentativeFirstName();

    /**
     * @return string
     */
    public function getLegalRepresentativeLastName();

    /**
     * @return Address|AddressInterface
     */
    public function getLegalRepresentativeAddress();

    /**
     * @return string
     */
    public function getLegalRepresentativeEmail();

    /**
     * @return \DateTime
     * timestamp
     */
    public function getLegalRepresentativeBirthday();

    /**
     * @return string|CountryInterface
     */
    public function getLegalRepresentativeNationality();

    /**
     * @return string|CountryInterface
     */
    public function getLegalRepresentativeCountryOfResidence();
}
