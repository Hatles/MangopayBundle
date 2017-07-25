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
     * @var string
     */
    private $loadableCallback;

    /**
     * @var string
     */
    private $dataTransformer;

    /**
     * MangoPayField constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (!array_key_exists('name', $options)) {
            $this->name = "";
        } else {
            $this->name = $options['name'];
        }

        if (array_key_exists('loadableCallback', $options)) {
            $this->loadableCallback = $options['loadableCallback'];
        }

        if (array_key_exists('dataTransformer', $options)) {
            $this->dataTransformer = $options['dataTransformer'];
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLoadableCallback()
    {
        return $this->loadableCallback;
    }

    /**
     * @return string
     */
    public function getDataTransformer()
    {
        return $this->dataTransformer;
    }
}
