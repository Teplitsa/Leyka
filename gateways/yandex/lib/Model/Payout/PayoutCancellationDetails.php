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

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\CancellationDetailsInterface;

/**
 * PayoutCancellationDetails - Комментарий к отмене выплаты
 *
 * @property string $party Инициатор отмены выплаты
 * @property string $reason Причина отмены выплаты
 */
class PayoutCancellationDetails extends AbstractObject implements CancellationDetailsInterface
{
    /**
     * @var string Инициатор отмены выплаты
     */
    private $_party = '';

    /**
     * @var string Причина отмены выплаты
     */
    private $_reason = '';

    /**
     * Возвращает участника процесса выплаты, который принял решение об отмене транзакции
     *
     * @return string Инициатор отмены выплаты
     */
    public function getParty()
    {
        return $this->_party;
    }

    /**
     * Возвращает причину отмены выплаты
     *
     * @return string Причина отмены выплаты
     */
    public function getReason()
    {
        return $this->_reason;
    }

    /**
     * Устанавливает участника процесса выплаты, который принял решение об отмене транзакции
     * @param $value
     */
    public function setParty($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty party value', 0, 'payout_cancellation_details.party');
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid party value type', 0, 'payout_cancellation_details.party', $value);
        } else {
            $this->_party = strtolower((string)$value);
        }
    }

    /**
     * Устанавливает причину отмены выплаты
     * @param $value
     */
    public function setReason($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty reason value', 0, 'payout_cancellation_details.reason');
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid reason value type', 0, 'payout_cancellation_details.reason');
        } else {
            $this->_reason = strtolower((string)$value);
        }
    }
}
