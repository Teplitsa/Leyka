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

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Класс получателя платежа.
 *
 * Получатель платежа нужен, если вы разделяете потоки платежей в рамках одного аккаунта или создаете платеж в адрес
 * другого аккаунта.
 *
 * @property string $accountId Идентификатор магазина
 * @property string $account_id Идентификатор магазина
 * @property string $gatewayId Идентификатор шлюза
 * @property string $gateway_id Идентификатор шлюза
 */
class Recipient extends AbstractObject implements RecipientInterface
{
    /**
     * @var string Идентификатор магазина
     */
    private $_accountId;

    /**
     * @var string Идентификатор шлюза. Используется для разделения потоков платежей в рамках одного аккаунта.
     */
    private $_gatewayId;

    /**
     * Возвращает идентификатор магазина
     *
     * @return string Идентификатор магазина
     */
    public function getAccountId()
    {
        return $this->_accountId;
    }

    /**
     * Устанавливает идентификатор магазина
     *
     * @param string $value Идентификатор магазина
     *
     * @throws EmptyPropertyValueException Выбрасывается если было передано пустое значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если было передано не строковое значение
     */
    public function setAccountId($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty accountId value in Recipient', 0, 'Recipient.accountId');
        } elseif (TypeCast::canCastToString($value)) {
            $this->_accountId = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid accountId value type in Recipient', 0, 'Recipient.accountId', $value
            );
        }
    }

    /**
     * Возвращает идентификатор шлюза.
     *
     * Идентификатор шлюза используется для разделения потоков платежей в рамках одного аккаунта.
     *
     * @return string Идентификатор шлюза
     */
    public function getGatewayId()
    {
        return $this->_gatewayId;
    }

    /**
     * Устанавливает идентификатор шлюза
     *
     * @param string $value Идентификатор шлюза
     *
     * @throws EmptyPropertyValueException Выбрасывается если было передано пустое значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если было передано не строковое значение
     */
    public function setGatewayId($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty gatewayId value in Recipient', 0, 'Recipient.gatewayId'
            );
        } elseif (TypeCast::canCastToString($value)) {
            $this->_gatewayId = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid gatewayId value type in Recipient', 0, 'Recipient.gatewayId', $value
            );
        }
    }
}
