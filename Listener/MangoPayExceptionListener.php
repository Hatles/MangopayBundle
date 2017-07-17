<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 13/07/2017
 * Time: 11:08
 */

namespace Troopers\MangopayBundle\Listener;


use MangoPay\Libraries\ResponseException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Troopers\MangopayBundle\Exception\MangoPayResponseException;

class MangoPayExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();

        if (!$exception instanceof ResponseException) {
            return;
        }

        throw new MangoPayResponseException($exception);
    }
}