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

namespace YooKassa\Model\PaymentData;

use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\PaymentMethodType;

/**
 * PaymentDataAlfabank
 * Платежные данные для проведения оплаты через Альфа Клик или Альфа Молнию.
 * @property string $login Имя пользователя в Альфа-Клике
 */
class PaymentDataAlfabank extends AbstractPaymentData
{
    /**
     * @var string Имя пользователя в Альфа-Клике
     */
    private $_login;

    public function __construct()
    {
        $this->_setType(PaymentMethodType::ALFABANK);
    }

    /**
     * Возвращает имя пользователя в Альфа-Клике
     * @return string Имя пользователя в Альфа-Клике
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * Устанавливает имя пользователя в Альфа-Клике
     * @param string $value Имя пользователя в Альфа-Клике
     */
    public function setLogin($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty login value', 0, 'PaymentDataAlfabank.login');
        } elseif (TypeCast::canCastToString($value)) {
            $this->_login = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid login value type', 0, 'PaymentDataAlfabank.login', $value
            );
        }
    }
}
