<?php

namespace MixplatClient\Method;

use MixplatClient\Configuration;

class StopSubscription extends MixplatMethod
{
    /**
     * ID подписки
     * @var string
     */
    public $recurrentId;


    /**
     * @return string
     */
    public function getMethod()
    {
        return 'stop_subscription';
    }

    /**
     * @param Configuration $config
     * @return array
     */
    public function getParams($config)
    {
        $signature = $this->encryptSignature(
            $this->recurrentId .
            $config->apiKey
        );

        $params = $this->parseParams();
        $params['signature'] = $signature;
        $params['api_version'] = $config->apiVersion;
        return $params;
    }
}
