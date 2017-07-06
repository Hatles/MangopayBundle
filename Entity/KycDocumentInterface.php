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
}