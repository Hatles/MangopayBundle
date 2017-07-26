<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 13/07/2017
 * Time: 11:34
 */

namespace Troopers\MangopayBundle\Exception;


use MangoPay\Libraries\ResponseException;

class MangoPayResponseException extends \RuntimeException
{
    /**
     * MangoPayResponseException constructor.
     * @param ResponseException $exception
     */
    public function __construct(ResponseException $exception)
    {
        $message = sprintf(
            'MangoPay\ResponseException Code: %s \n Message: %s \n Details: %s',
            $exception->getCode(),
            $exception->getMessage(),
            $exception->GetErrorDetails()
        );

        parent::__construct($message, $exception->getCode(), $exception);
    }
}
