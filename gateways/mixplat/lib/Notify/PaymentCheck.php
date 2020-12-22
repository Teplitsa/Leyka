<?php

namespace MixplatClient\Notify;


class PaymentCheck extends MixplatNotify
{
    /**
     * Версия API, всегда "3".
     * @var int
     */
    public $apiVersion;

    /**
     * Тип уведомления. Всегда "payment_check".
     * @var string
     */
    public $request;

    /**
     * ID платежа в MIXPLAT.
     * @var string
     */
    public $paymentId;

    /**
     * ID проекта в MIXPLAT, для которого был создан платёж.
     * @var int
     */
    public $projectId;

    /**
     * Признак тестового платежа.
     *  1: Платёж тестовый
     *  0: Платёж реальный
     * @var int
     */
    public $test;

    /**
     * ID платежа в ТСП, если он был указан при создании платежа.
     * @var string|null
     */
    public $merchantPaymentId;

    /**
     * Произвольные данные ТСП, связанные с платежом, если они были указаны при создании платежа.
     * @var string|null
     */
    public $merchantData;

    /**
     * Дата и время создания платежа (по Москве).
     * Формат: YYYY-MM-DD HH:MM:SS
     * @var string
     */
    public $dateCreated;

    /**
     * Валюта платежа.
     * \MixplatClient\MixplatVars::CURRENCY_*
     * @var string
     */
    public $currency;

    /**
     * Сумма платежа (в минорных единицах, копейках).
     * @var int
     */
    public $amount;

    /**
     * Платёжный метод, который был использован для оплаты.
     * \MixplatClient\MixplatVars::PAYMENT_METHOD_*
     * @var string|null
     */
    public $paymentMethod;

    /**
     * Номер телефона абонента.
     * @var string
     */
    public $userPhone;

    /**
     * Дополнительная информация о платеже, специфичная для группы платёжных методов "mobile".
     * Присутствует только для платежей с платёжным методом "mobile".
     * @var array|null
     */
    public $mobile;

    /**
     * Дополнительная информация о платеже, специфичная для группы платёжных методов "card".
     * Присутствует только для платежей с платёжным методом "card".
     * @var array|null
     */
    public $card;

    /**
     * Дополнительная информация о платеже, специфичная для группы платёжных методов "wallet".
     * Присутствует только для платежей с платёжным методом "wallet".
     * @var array|null
     */
    public $wallet;

    /**
     * Дополнительная информация о платеже, специфичная для группы платёжных методов "bank".
     * Присутствует только для платежей с платёжным методом "bank".
     * @var array|null
     */
    public $bank;


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
            $this->paymentId .
            $config->apiKey
        );

        if ($signature === $this->signature) {
            return true;
        }

        return false;
    }

}
