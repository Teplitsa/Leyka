<?php

namespace MixplatClient\Method;

use MixplatClient\Configuration;

class GetRefundStatus extends MixplatMethod
{
    /**
     * ID возврата в MIXPLAT
     * @var string
     */
    public $refundId;


    /**
     * @return string
     */
    public function getMethod()
    {
        return 'get_refund_status';
    }

    /**
     * @param Configuration $config
     * @return array
     */
    public function getParams($config)
    {
        $signature = $this->encryptSignature(
            $this->refundId .
            $config->apiKey
        );

        $params = $this->parseParams();
        $params['signature'] = $signature;
        $params['api_version'] = $config->apiVersion;
        return $params;
    }
}
