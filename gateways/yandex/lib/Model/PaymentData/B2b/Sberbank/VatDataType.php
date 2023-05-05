<?php

/**
 * The MIT License
 *
 * Copyright (c) 2022 "YooMoney", NBСO LLC
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

namespace YooKassa\Model\PaymentData\B2b\Sberbank;

use YooKassa\Common\AbstractEnum;

/**
 * PaymentDataB2bSberbankVatDataType - Способ расчёта НДС
 * |Код|Описание|
 * --- | ---
 * |calculated|Сумма НДС включена в сумму платежа|
 * |mixed|Разные ставки НДС для разных товаров|
 * |untaxed|Сумма платежа НДС не облагается|
 */
class VatDataType extends AbstractEnum
{
    /** Сумма НДС включена в сумму платежа */
    const CALCULATED = 'calculated';
    /** Разные ставки НДС для разных товаров */
    const MIXED      = 'mixed';
    /** Сумма платежа НДС не облагается */
    const UNTAXED    = 'untaxed';

    protected static $validValues = array(
        self::CALCULATED => true,
        self::MIXED      => true,
        self::UNTAXED    => true,
    );

}
