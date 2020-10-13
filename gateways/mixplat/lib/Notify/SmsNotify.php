<?php

namespace MixplatClient\Notify;


class SmsNotify extends MixplatNotify
{
    /**
     * Версия API, всегда "3".
     * @var int
     */
    public $apiVersion;

    /**
     * Тип уведомления. Всегда "sms".
     * @var string
     */
    public $request;

    /**
     * ID проекта в MIXPLAT, для которого был создан платёж.
     * @var int
     */
    public $projectId;

    /**
     * ID sms в MIXPLAT.
     * @var int
     */
    public $smsId;

    /**
     * Номер телефона в международном формате без символа "+".
     * @var string
     */
    public $userPhone;

    /**
     * Дополнительная информация о платеже, специфичная для группы платёжных методов "mobile".
     * @var array
     */
    public $mobile;


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
            $this->smsId .
            $config->apiKey
        );

        if ($signature === $this->signature) {
            return true;
        }

        return false;
    }

}
