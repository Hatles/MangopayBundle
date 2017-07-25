<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 24/07/2017
 * Time: 19:29
 */

namespace Troopers\MangopayBundle\Entity;


interface CountryInterface
{
    /**
     * @return string
     */
    public function getCountryCode();
}