<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 26/07/2017
 * Time: 15:09
 */

namespace Troopers\MangopayBundle\DataTransformer;


interface DataTransformerInterface
{
    /**
     * @param $value
     * @return mixed
     */
    public function transform($value);

    /**
     * @return string
     */
    public function getName();
}
