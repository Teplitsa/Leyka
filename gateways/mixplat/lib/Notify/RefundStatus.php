<?php

namespace MixplatClient\Notify;


class RefundStatus extends MixplatNotify
{
    /**
     * Версия API, всегда "3".
     * @var int
     */
    public $apiVersion;

    /**
     * Тип уведомления. Всегда "refund_status".
     * @var string
     */
    public $request;

    /**
     * ID возврата в MIXPLAT.
     * @var int
     */
    public $refundId;

    /**
     * ID платежа в MIXPLAT, для которого был создан возврат.
     * @var string
     */
    public $paymentId;

    /**
     * Сумма возврата (в минорных единицах, копейках).
     * @var int
     */
    public $amount;

    /**
     * ID возврата в ТСП, или NULL, если он не был указан при создании платежа.
     * @var string|null
     */
    public $merchantRefundId;

    /**
     * Произвольные данные ТСП, связанные с возвратом, или NULL, если они не были указаны при создании платежа.
     * @var string|null
     */
    public $merchantData;

    /**
     * Статус платежа.
     * \MixplatClient\MixplatVars::REFUND_STATUS_*
     * @var string
     */
    public $status;

    /**
     * Дата и время создания возврата (по Москве).
     * Формат: YYYY-MM-DD HH:MM:SS
     * @var string
     */
    public $dateCreated;

    /**
     * Дата и время осуществления возврата (по Москве), или NULL, если возврат ещё обрабатывается.
     * Формат: YYYY-MM-DD HH:MM:SS
     * @var string|null
     */
    public $dateCompleted;

    /**
     * Signature.
     * @var string
     */
    public $signature;

    /**
     * @param \MixplatClient\Configuration|null $config
     * @return bool|void
     */
    public function checkSignature($config)
    {
        $signature = $this->encryptSignature(
            $this->refundId .
            $config->apiKey
        );

        if ($signature === $this->signature) {
            return true;
        }

        return false;
    }

}
