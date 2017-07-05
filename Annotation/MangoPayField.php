<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 05/07/2017
 * Time: 14:32
 */

namespace Troopers\MangopayBundle\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class MangoPayField
{
    /**
     * @var string
     */
    private $name;

    /**
     * MangoPayField constructor.
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