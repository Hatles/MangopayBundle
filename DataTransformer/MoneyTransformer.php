<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 26/07/2017
 * Time: 15:10
 */

namespace Troopers\MangopayBundle\DataTransformer;


use MangoPay\Money;

class MoneyTransformer implements DataTransformerInterface
{

    /**
     * @param Money $value
     * @return integer
     */
    public function transform($value)
    {
        if ($value instanceof Money)
        {
            return $value->Amount;
        }

        throw new \InvalidArgumentException('value must be a Money, '.get_class($value).' given');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'money';
    }
}
