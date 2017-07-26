<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 26/07/2017
 * Time: 15:10
 */

namespace Troopers\MangopayBundle\DataTransformer;


class TimestampTransformer implements DataTransformerInterface
{
    /**
     * @param int $value
     * @return \DateTime
     */
    public function transform($value)
    {
        $dtStr = date("c", $value);
        return new \DateTime($dtStr);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'date';
    }
}
