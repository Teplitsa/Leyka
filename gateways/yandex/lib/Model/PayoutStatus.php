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

namespace YooKassa\Model;

use YooKassa\Common\AbstractEnum;

/**
 * PayoutStatus - Статус выплаты
 * |Код|Описание|
 * --- | ---
 * |pending|Выплата создана и ожидает подтверждения от эмитента|
 * |succeeded|Выплата успешно завершена|
 * |canceled|Выплата отменена|
 *
 */
class PayoutStatus extends AbstractEnum
{
    /** Только для выплат на банковские карты: выплата создана и ожидает подтверждения от эмитента, что деньги можно перевести на указанную банковскую карту */
    const PENDING = 'pending';
    /** Выплата успешно завершена, оплата переведена на платежное средство продавца (финальный и неизменяемый статус) */
    const SUCCEEDED = 'succeeded';
    /** Выплата отменена, инициатор и причина отмены указаны в объекте cancellation_details (финальный и неизменяемый статус) */
    const CANCELED = 'canceled';

    protected static $validValues = array(
        self::PENDING => true,
        self::SUCCEEDED => true,
        self::CANCELED => true,
    );
}
