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
 * ReceiptType - Тип чека в онлайн-кассе.
 * |Код|Описание|
 * --- | ---
 * |payment|Приход|
 * |refund|Возврат|
 * |simple|Простой|
 */
class ReceiptType extends AbstractEnum
{
    /** @var string Тип чека: приход */
    const PAYMENT = 'payment';

    /** @var string Тип чека: возврат */
    const REFUND  = 'refund';

    /** @var string Тип чека: простой */
    const SIMPLE  = 'simple';

    protected static $validValues = array(
        self::PAYMENT => true,
        self::REFUND  => true,
        self::SIMPLE  => true,
    );
}
