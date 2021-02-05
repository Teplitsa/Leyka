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

namespace YooKassa\Model\PaymentData;

use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\PaymentMethodType;

/**
 * PaymentDataGooglePay
 * Платежные данные для проведения оплаты при помощи Google Pay.
 * @property string $paymentMethodToken Криптограмма Payment Token Cryptography для проведения оплаты через Google Pay
 * @property string $googleTransactionId Уникальный идентификатор транзакции, выданный Google
 */
class PaymentDataGooglePay extends AbstractPaymentData
{
    /**
     * @var string Криптограмма Payment Token Cryptography для проведения оплаты через Google Pay
     */
    private $_paymentMethodToken;

    /**
     * @var string Уникальный идентификатор транзакции, выданный Google
     */
    private $_googleTransactionId;

    public function __construct()
    {
        $this->_setType(PaymentMethodType::GOOGLE_PAY);
    }

    /**
     * @return string Криптограмма Payment Token Cryptography для проведения оплаты через Google Pay
     */
    public function getPaymentMethodToken()
    {
        return $this->_paymentMethodToken;
    }

    /**
     * @param string $value Криптограмма Payment Token Cryptography для проведения оплаты через Google Pay
     */
    public function setPaymentMethodToken($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for paymentMethodToken', 0, 'PaymentDataGooglePay.paymentMethodToken'
            );
        } elseif (TypeCast::canCastToString($value)) {
            $this->_paymentMethodToken = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for paymentMethodToken', 0, 'PaymentDataGooglePay.paymentMethodToken', $value
            );
        }
    }

    /**
     * @return string Уникальный идентификатор транзакции, выданный Google
     */
    public function getGoogleTransactionId()
    {
        return $this->_googleTransactionId;
    }

    /**
     * @param string $value Уникальный идентификатор транзакции, выданный Google
     */
    public function setGoogleTransactionId($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for googleTransactionId', 0, 'PaymentDataGooglePay.googleTransactionId'
            );
        } elseif (TypeCast::canCastToString($value)) {
            $this->_googleTransactionId = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for googleTransactionId', 0, 'PaymentDataGooglePay.googleTransactionId', $value
            );
        }
    }
}
