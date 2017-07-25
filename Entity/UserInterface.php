<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 25/07/2017
 * Time: 09:27
 */

namespace Troopers\MangopayBundle\Entity;


interface UserInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return int
     */
    public function getMangoUserId();

    /**
     * @param int $userId
     */
    public function setMangoUserId($userId);
}