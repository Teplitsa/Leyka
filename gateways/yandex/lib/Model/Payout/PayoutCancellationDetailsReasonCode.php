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

namespace YooKassa\Model\Payout;

use YooKassa\Common\AbstractEnum;

/**
 * PayoutCancellationDetailsReasonCode - Возможные причины отмены выплаты
 */
class PayoutCancellationDetailsReasonCode extends AbstractEnum
{
    /** Выплата заблокирована из-за подозрения в мошенничестве */
    const FRAUD_SUSPECTED = 'fraud_suspected';
    /** Причина не детализирована. Пользователю следует обратиться к инициатору отмены выплаты за уточнением подробностей */
    const GENERAL_DECLINE = 'general_decline';
    /** Превышен лимит на разовое зачисление */
    const ONE_TIME_LIMIT_EXCEEDED = 'one_time_limit_exceeded';
    /** Превышен лимит выплат за период времени (сутки, месяц) */
    const PERIODIC_LIMIT_EXCEEDED = 'periodic_limit_exceeded';
    /** Эмитент отклонил выплату по неизвестным причинам */
    const REJECTED_BY_PAYEE = 'rejected_by_payee';

    protected static $validValues = array(
        self::FRAUD_SUSPECTED               => true,
        self::GENERAL_DECLINE               => true,
        self::ONE_TIME_LIMIT_EXCEEDED       => true,
        self::PERIODIC_LIMIT_EXCEEDED       => true,
        self::REJECTED_BY_PAYEE             => true,
    );
}
