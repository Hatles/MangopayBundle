<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 06/07/2017
 * Time: 10:30
 */

namespace Troopers\MangopayBundle\Entity;


interface KycDocumentInterface
{
    const TYPE_IDENTITY_PROOF = 'IDENTITY_PROOF';
    const TYPE_REGISTRATION_PROOF = 'REGISTRATION_PROOF';
    const TYPE_ARTICLES_OF_ASSOCIATION = 'ARTICLES_OF_ASSOCIATION';
    const TYPE_SHAREHOLDER_DECLARATION = 'SHAREHOLDER_DECLARATION';
    const TYPE_ADDRESS_PROOF = 'ADDRESS_PROOF';

    /**
     * @return UserInterface
     */
    public function getUser();

    /**
     * @param UserInterface $user
     */
    public function setUser($user);

    /**
     * @return int
     */
    public function getKycDocumentId();

    /**
     * @param int $documentId
     */
    public function setKycDocumentId($documentId);

    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getPages();
}