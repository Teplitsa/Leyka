<?php

namespace MixplatClient\Method;

use MixplatClient\Configuration;

class RefundPayment extends MixplatMethod
{
    /**
     * ID платежа в MIXPLAT, по которому будет осуществлён возврат
     * @var string
     */
    public $paymentId;

    /**
     * Сумма возврата (в минорных единицах, копейках).
     * Необязательный, по умолчанию совпадает с суммой платежа с идентификатором payment_id
     * @var int|null
     */
    public $amount;

    /**
     * Валюта платежа
     * Необязательный, по умолчанию совпадает с валютой платежа с идентификатором payment_id.
     * \MixplatClient\MixplatVars::CURRENCY_*
     * @var string|null
     */
    public $currency;

    /**
     * ID возврата в ТСП
     * От 1 до 256 символов
     * Необязательный параметр.
     * @var string|null
     */
    public $merchantRefundId;

    /**
     * Произвольные данные ТСП, связанные с возвратом
     * От 1 до 256 символов.
     * Необязательный параметр.
     * @var string|null
     */
    public $merchantData;


    /**
     * @return string
     */
    public function getMethod()
    {
        return 'refund_payment';
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
