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

namespace YooKassa\Request\Payouts\PayoutDestinationData;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Класс PayoutDestinationDataBankCardCard
 *
 * Данные банковской карты
 * @property string $number Номер банковской карты. Формат: только цифры, без пробелов.
 */
class PayoutDestinationDataBankCardCard extends AbstractObject
{
    /**
     * @var string Номер банковской карты. Формат: только цифры, без пробелов.
     */
    private $_number;

    /**
     * Возвращает последние 4 цифры номера карты
     * @return string Последние 4 цифры номера карты
     */
    public function getNumber()
    {
        return $this->_number;
    }

    /**
     * Устанавливает последние 4 цифры номера карты
     * @param string $value Последние 4 цифры номера карты
     */
    public function setNumber($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty card number value', 0, 'PayoutDestinationBankCardCard.number');
        } elseif (TypeCast::canCastToString($value)) {
            if (preg_match('/^[0-9]+$/', (string)$value)) {
                $this->_number = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid card number value', 0, 'PayoutDestinationBankCardCard.number', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid card number value type', 0, 'PayoutDestinationBankCardCard.number', $value
            );
        }
    }

}
