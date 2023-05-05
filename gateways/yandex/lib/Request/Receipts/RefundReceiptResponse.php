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

namespace YooKassa\Request\Receipts;

use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Класс описывающий чек, привязанный к возврату
 *
 * @package YooKassa
 *
 * @property string $refund_id Идентификатор возврата в ЮKassa.
 * @property string $refundId Идентификатор возврата в ЮKassa.
 */
class RefundReceiptResponse extends AbstractReceiptResponse
{
    /** Длина идентификатора возврата */
    const LENGTH_REFUND_ID = 36;

    private $_refund_id;

    /**
     * Установка свойств, присущих конкретному объекту
     *
     * @param array $receiptData
     *
     * @return void
     */
    public function setSpecificProperties($receiptData)
    {
        $this->setRefundId($this->getObjectId());
    }

    /**
     * Возвращает идентификатор возврата
     *
     * @return string Идентификатор возврата
     */
    public function getRefundId()
    {
        return $this->_refund_id;
    }

    /**
     * Устанавливает идентификатор возврата в ЮKassa
     *
     * @param string $value Идентификатор возврата в ЮKassa
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве значения была передана не строка
     * @throws InvalidPropertyValueException Выбрасывается если длина переданной строки не равна 36
     */
    public function setRefundId($value)
    {
        if ($value === null || $value === '') {
            $this->_refund_id = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid refund_id value type', 0, 'Receipt.refundId');
        } elseif (strlen((string)$value) !== self::LENGTH_REFUND_ID) {
            throw new InvalidPropertyValueException(
                'Invalid refund_id value: "'.$value.'"', 0, 'Receipt.refundId', $value
            );
        } else {
            $this->_refund_id = (string)$value;
        }
    }

}
