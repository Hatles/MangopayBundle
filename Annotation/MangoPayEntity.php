<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 05/07/2017
 * Time: 14:31
 */

namespace Troopers\MangopayBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class MangoPayEntity
{
    const ENTITY_USER = 'entity.user';
    const ENTITY_WALLET = 'entity.wallet';
    const ENTITY_BANKINFORMATION = 'entity.bank_information';

    /**
     * MangoPayEntity constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
    }
}