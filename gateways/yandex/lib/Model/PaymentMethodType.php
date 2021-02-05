<?php

/**
 * The MIT License
 *
 * Copyright (c) 2020 "YooMoney", NBСO LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace YooKassa\Model;

use YooKassa\Common\AbstractEnum;

/**
 * PaymentMethodType - Тип источника средств для проведения платежа
 * |Код|Описание|
 * --- | ---
 * |yoo_money|Платеж из кошелька ЮMoney|
 * |bank_card|Платеж с произвольной банковской карты|
 * |sberbank|Платеж СбербанкОнлайн|
 * |cash|Платеж наличными|
 * |mobile_balance|Платеж с баланса мобильного телефона|
 * |apple_pay|Платеж ApplePay|
 * |google_pay|Платеж Google Pay|
 * |qiwi|Платеж из кошелька Qiwi|
 * |installments|Заплатить по частям|
 * |b2b_sberbank|Сбербанк Бизнес Онлайн|
 * |tinkoff_bank|Интернет-банк Тинькофф|
 * |psb|ПромсвязьБанк|
 * |wechat|Платеж через WeChat|
 */
class PaymentMethodType extends AbstractEnum
{
    const YOO_MONEY      = 'yoo_money';
    const BANK_CARD      = 'bank_card';
    const SBERBANK       = 'sberbank';
    const CASH           = 'cash';
    const MOBILE_BALANCE = 'mobile_balance';
    const APPLE_PAY      = 'apple_pay';
    const GOOGLE_PAY     = 'google_pay';
    const QIWI           = 'qiwi';
    const WEBMONEY       = 'webmoney';
    const ALFABANK       = 'alfabank';
    const INSTALLMENTS   = 'installments';
    const B2B_SBERBANK   = 'b2b_sberbank';
    const TINKOFF_BANK   = 'tinkoff_bank';
    const PSB            = 'psb';
    /** @deprecated Будет удален в следующих версиях */
    const WECHAT         = 'wechat';

    protected static $validValues = array(
        self::YOO_MONEY   => true,
        self::BANK_CARD      => true,
        self::SBERBANK       => true,
        self::CASH           => true,
        self::MOBILE_BALANCE => false,
        self::APPLE_PAY      => false,
        self::GOOGLE_PAY     => false,
        self::QIWI           => true,
        self::WEBMONEY       => true,
        self::ALFABANK       => true,
        self::TINKOFF_BANK   => true,
        self::INSTALLMENTS   => true,
        self::B2B_SBERBANK   => true,
        self::PSB            => false,
        self::WECHAT         => true,
    );
}
