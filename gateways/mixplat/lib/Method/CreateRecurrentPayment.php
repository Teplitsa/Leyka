<?php

namespace MixplatClient\Method;

use MixplatClient\Configuration;

class CreateRecurrentPayment extends MixplatMethod
{
    /**
     * Уникальный идентификатор запроса, задаваемый ТСП, обеспечивающий идемпотентность вызовов
     * (повторные запросы с тем же request_id не будут приводить к созданию нового платежа,
     * а параметры ответа будут полностью повторять параметры ответа первоначального вызова с данным request_id).
     * Рекомендуется передавать этот параметр, чтобы защититься от дублирования платежей в результате сетевых проблем,
     * задержек ответа и т. п.
     * В качестве request_id можно использовать идентификатор платежа в системе ТСП (если он уникален),
     * или хеш от ключевых параметров запроса.
     * Проверка наличия другого запроса с данным request_id осуществляется за последние 30 дней.
     * От 1 до 64 символов. Необязательный параметр.
     * @var string|null
     */
    public $requestId;

    /**
     * ID привязанной карты, полученный ранее в уведомлении payment_status после инициации установочного платежа
     * методом create_payment_form или create_payment
     * @var string|null
     */
    public $recurrentId;

    /**
     * ID платежа в ТСП
     * От 1 до 256 символов. Необязательный параметр.
     * @var string|null
     */
    public $merchantPaymentId;

    /**
     * Произвольные данные ТСП, связанные с платежом.
     * От 1 до 256 символов. Необязательный параметр.
     * @var string|null
     */
    public $merchantData;


    /**
     * Сумма платежа (в минорных единицах, копейках).
     * От 100 до 5000000. Обязательный параметр.
     * @var int
     */
    public $amount;

    /**
     * Данные для чека
     * Необязательный параметр.
     * @var array|null
     */
    public $items;


    /**
     * @return string
     */
    public function getMethod()
    {
        return 'create_recurrent_payment';
    }

    /**
     * @param Configuration $config
     * @return array
     */
    public function getParams($config)
    {
        $signature = $this->encryptSignature(
            $this->requestId .
            $config->projectId .
            $this->merchantPaymentId .
            $config->apiKey
        );

        $params = $this->parseParams();
        $params['signature'] = $signature;
        $params['api_version'] = $config->apiVersion;

        return $params;
    }
}
