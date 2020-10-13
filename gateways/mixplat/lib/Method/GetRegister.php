<?php

namespace MixplatClient\Method;

use MixplatClient\Configuration;

class GetRegister extends MixplatMethod
{
    /**
     * День, или месяц, за который требуется реестр.
     * День (в формате YYYY-MM-DD) для получения реестра за указанный день
     * Месяц (в формате YYYY-MM) для получения реестра за указанный месяц
     * @var string
     */
    public $period;

    /**
     * Тип выгружаемого реестра, одно из:
     *  - payment (выгрузка платежей)
     *  - refund (выгрузка возвратов)
     *  - sms (выгрузка смс)
     * @var string
     */
    public $type;


    /**
     * @return string
     */
    public function getMethod()
    {
        return 'get_register';
    }

    /**
     * @param Configuration $config
     * @return array
     */
    public function getParams($config)
    {
        $signature = $this->encryptSignature(
            $config->companyId .
            $config->companyApiKey
        );

        $params = $this->parseParams();
        $params['signature'] = $signature;
        $params['api_version'] = $config->apiVersion;
        $params['company_id'] = $config->companyId;
        return $params;
    }
}
