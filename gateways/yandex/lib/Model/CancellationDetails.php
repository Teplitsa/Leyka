<?php

/**
 * The MIT License
 *
 * Copyright (c) 2017 NBCO Yandex.Money LLC
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

namespace YandexCheckout\Model;

use YandexCheckout\Common\AbstractObject;
use YandexCheckout\Common\Exceptions\EmptyPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException;
use YandexCheckout\Helpers\TypeCast;

/**
 * CancellationDetails - Комментарий к отмене платежа
 *
 * @property string $party Инициатор отмены платежа
 * @property string $reason Причина отмены платежа
 */
class CancellationDetails extends AbstractObject implements CancellationDetailsInterface
{
    /**
     * @var string Инициатор отмены платежа
     */
    private $_party = '';

    /**
     * @var string Причина отмены платежа
     */
    private $_reason = '';

    /**
     * CancellationDetails constructor.
     * @param string|null $party Инициатор отмены платежа
     * @param string|null $reason Причина отмены платежа
     */
    public function __construct($party = null, $reason = null)
    {
        if ($party !== null) {
            $this->setParty($party);
        }
        if ($reason !== null) {
            $this->setReason($reason);
        }
    }

    /**
     * Возвращает участника процесса платежа, который принял решение об отмене транзакции
     *
     * @return string Инициатор отмены платежа
     */
    public function getParty()
    {
        return $this->_party;
    }

    /**
     * Возвращает причину отмены платежа
     *
     * @return string Причина отмены платежа
     */
    public function getReason()
    {
        return $this->_reason;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'party'    => $this->_party,
            'reason' => $this->_reason,
        );
    }

    /**
     * Устанавливает участника процесса платежа, который принял решение об отмене транзакции
     * @param $value
     */
    public function setParty($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty party value', 0, 'cancellation_details.party');
        }
        if (TypeCast::canCastToEnumString($value)) {
            $value = strtolower((string)$value);
            if (CancellationDetailsPartyCode::valueExists($value)) {
                $this->_party = $value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid party value: "'.$value.'"', 0, 'cancellation_details.party', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException('Invalid party value type', 0, 'cancellation_details.party',
                $value);
        }
    }

    /**
     * Устанавливает причину отмены платежа
     * @param $value
     */
    public function setReason($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty reason value', 0, 'cancellation_details.reason');
        }
        if (TypeCast::canCastToEnumString($value)) {
            $value = strtolower((string)$value);
            if (CancellationDetailsReasonCode::valueExists($value)) {
                $this->_reason = $value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid reason value: "'.$value.'"', 0, 'cancellation_details.reason', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException('Invalid reason value type', 0, 'cancellation_details.reason',
                $value);
        }
    }
}