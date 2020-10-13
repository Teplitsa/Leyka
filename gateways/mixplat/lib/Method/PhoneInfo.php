<?php

namespace MixplatClient\Method;

use MixplatClient\Configuration;

class PhoneInfo extends MixplatMethod
{
    /**
     * Номер телефона в международном формате без символа "+".
     * @var string
     */
    public $userPhone;


    /**
     * @return string
     */
    public function getMethod()
    {
        return 'phone_info';
    }

    /**
     * @param Configuration $config
     * @return array
     */
    public function getParams($config)
    {
        $signature = $this->encryptSignature(
            $config->projectId .
            $this->userPhone .
            $config->apiKey
        );

        $params = $this->parseParams();
        $params['signature'] = $signature;
        $params['api_version'] = $config->apiVersion;
        $params['project_id'] = $config->projectId;
        return $params;
    }
}
