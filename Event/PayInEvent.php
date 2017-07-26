<?php

namespace Troopers\MangopayBundle\Event;

use MangoPay\CardPreAuthorization;
use MangoPay\PayIn;
use Symfony\Component\EventDispatcher\Event;

class PayInEvent extends Event
{
    private $payIn;
    private $preAuth;

    public function __construct(PayIn $payIn, CardPreAuthorization $preAuth = null)
    {
        $this->payIn = $payIn;
        $this->preAuth = $preAuth;
    }

    /**
     * Get payin.
     *
     * @return PayIn
     */
    public function getPayIn()
    {
        return $this->payIn;
    }

    /**
     * Set payin.
     *
     * @param PayIn $payIn
     *
     * @return PayInEvent
     */
    public function setPayIn($payIn)
    {
        $this->payIn = $payIn;

        return $this;
    }

    /**
     * @return CardPreAuthorization
     */
    public function getPreAuth()
    {
        return $this->preAuth;
    }

    /**
     * @param CardPreAuthorization $preAuth
     */
    public function setPreAuth($preAuth)
    {
        $this->preAuth = $preAuth;
    }
}
