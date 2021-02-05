<?php


namespace YooKassa\Model\PaymentMethod;


use YooKassa\Common\AbstractEnum;

/**
 * BankCardSource - Источник данных банковской карты
 * |Код|Описание|
 * --- | ---
 * |apple_pay|Источник данных apple_pay|
 * |google_pay|Источник данных google_pay|
 *
 */
class BankCardSource extends AbstractEnum
{
    const APPLE_PAY  = 'apple_pay';
    const GOOGLE_PAY = 'google_pay';

    protected static $validValues = array(
        self::APPLE_PAY  => true,
        self::GOOGLE_PAY => true,
    );
}