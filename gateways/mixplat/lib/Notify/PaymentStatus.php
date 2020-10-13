<?php

namespace MixplatClient\Notify;


class PaymentStatus extends MixplatNotify
{
    /**
     * Версия API, всегда "3".
     * @var int
     */
    public $apiVersion;

    /**
     * Тип уведомления. Всегда "payment_status".
     * @var string
     */
    public $request;

    /**
     * ID платежа в MIXPLAT.
     * @var string
     */
    public $paymentId;

    /**
     * ID привязанной карты, далее вы можете использовать этот идентификатор и инициировать повторные платежи
     * методом create_recurrent_payment без необходимости подтверждения со стороны плательщика.
     * Присутствует только в случае успешного выполнения метода (result = ok) и подключенного в настройках
     * платёжной формы режима "подписка" или "рекуррентные платежи".
     * @var string|null
     */
    public $recurrentId;

    /**
     * ID проекта в MIXPLAT, для которого был создан платёж.
     * @var int
     */
    public $projectId;

    /**
     * ID платёжной формы, созданной для этого проекта в ЛК MIXPLAT.
     * @var int|null
     */
    public $paymentFormId;

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
     * Массив дополнительных сведений о транзакции, которые были переданы при создании платежа
     * методом create_payment_form или методом create_payment.
     * Может применяться для передачи сопутствующих данных о плательщике или товаре: по значениям в массиве
     * возможна фильтрация платежей в личном кабинете и выгружаемых XLS отчетах.
     * @var array|null
     */
    public $merchantFields;

    /**
     * Статус платежа.
     * \MixplatClient\MixplatVars::PAYMENT_STATUS_*
     * @var string
     */
    public $status;

    /**
     * Расширенный статус платежа.
     * \MixplatClient\MixplatVars::PAYMENT_EXT_STATUS_*
     * @var string
     */
    public $statusExtended;

    /**
     * Дата и время создания платежа (по Москве).
     * Формат: YYYY-MM-DD HH:MM:SS
     * @var string
     */
    public $dateCreated;

    /**
     * Дата и время обработки платежа (по Москве).
     * Формат: YYYY-MM-DD HH:MM:SS
     * @var string
     */
    public $dateProcessed;

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
     * Сумма, фактически оплаченная плательщиком в минорных единицах (копейках) или NULL, если платёж ещё не подтверждён.
     * @var int|null
     */
    public $amountUser;

    /**
     * Сумма к выплате ТСП в минорных единицах (копейках) или NULL, если платёж ещё не подтверждён.
     * @var int|null
     */
    public $amountMerchant;

    /**
     * Номер телефона абонента, если был передан в запросе create_payment или create_payment_form.
     * @var string|null
     */
    public $userPhone;

    /**
     * Платёжный метод, который был использован для оплаты, или NULL, если он ещё не определён.
     * \MixplatClient\MixplatVars::PAYMENT_METHOD_*
     * @var string|null
     */
    public $paymentMethod;

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
