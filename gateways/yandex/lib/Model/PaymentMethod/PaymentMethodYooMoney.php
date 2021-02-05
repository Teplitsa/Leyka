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

namespace YooKassa\Model\PaymentMethod;

use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\PaymentMethodType;

/**
 * PaymentMethodYooMoney
 * Объект, описывающий метод оплаты, при оплате через ЮMoney
 * @property string $type Тип объекта
 * @property string $accountNumber Номер кошелька в ЮMoney с которого была произведена оплата.
 * @property string $account_number Номер кошелька в ЮMoney с которого была произведена оплата.
 */
class PaymentMethodYooMoney extends AbstractPaymentMethod
{
    /**
     * @var string Номер кошелька в ЮMoney с которого была произведена оплата.
     */
    private $_accountNumber;

    public function __construct()
    {
        $this->_setType(PaymentMethodType::YOO_MONEY);
    }

    /**
     * @return string Номер кошелька в ЮMoney с которого была произведена оплата.
     */
    public function getAccountNumber()
    {
        return $this->_accountNumber;
    }

    /**
     * @param string $value Номер кошелька в ЮMoney с которого была произведена оплата.
     */
    public function setAccountNumber($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty accountNumber value', 0, 'PaymentMethodYooMoney.accountNumber'
            );
        } elseif (TypeCast::canCastToString($value)) {
            if (preg_match('/^[0-9]{11,33}$/', $value)) {
                $this->_accountNumber = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid accountNumber value', 0, 'PaymentMethodYooMoney.accountNumber', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid accountNumber value type', 0, 'PaymentMethodYooMoney.accountNumber', $value
            );
        }
    }
}
