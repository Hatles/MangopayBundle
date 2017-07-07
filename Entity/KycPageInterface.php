<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 06/07/2017
 * Time: 10:31
 */

namespace Troopers\MangopayBundle\Entity;


interface KycPageInterface
{
    /**
     * @return string
     *      The base64 encoded file which needs to be uploaded
     */
    public function getFileBase64();
}