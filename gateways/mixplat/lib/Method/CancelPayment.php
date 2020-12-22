<?php

namespace MixplatClient\Method;

use MixplatClient\Configuration;

class CancelPayment extends MixplatMethod
{
    /**
     * ID платежа в MIXPLAT
     * @var string
     */
    public $paymentId;


    /**
     * @return string
     */
    public function getMethod()
    {
        return 'cancel_payment';
    }

    /**
     * @param Configuration $config
     * @return array
     */
    public function getParams($config)
    {
        $signature = $this->encryptSignature(
            $this->paymentId .
            $config->apiKey
        );

        $params = $this->parseParams();
        $params['signature'] = $signature;
        $params['api_version'] = $config->apiVersion;

        return $params;
    }
}
