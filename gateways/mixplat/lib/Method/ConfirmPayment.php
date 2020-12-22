<?php

namespace MixplatClient\Method;

use MixplatClient\Configuration;

class ConfirmPayment extends MixplatMethod
{
    /**
     * ID платежа в MIXPLAT
     * @var string
     */
    public $paymentId;

    /**
     * Сумма списания (в минорных единицах, копейках)
     * От 100 до 50000000 для payment_method = card
     * Необязательный параметр
     * @var int|null
     */
    public $amount;

    /**
     * @return string
     */
    public function getMethod()
    {
        return 'confirm_payment';
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
