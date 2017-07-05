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
abstract class MangoPayEntity
{
    const ENTITY_USER = 'entity.user';
    const ENTITY_WALLET = 'entity.wallet';
    const ENTITY_BANKINFORMATION = 'entity.bank_information';

    /**
     * @var string
     */
    private $name;

    /**
     * MangoPayEntity constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (empty($options['name'])) {
            throw new \InvalidArgumentException("L'annotation MangoPayField doit avoir un attribut 'name'");
        }

        $this->name = $options['name'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}