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
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\PaymentData\B2b\Sberbank\VatData;
use YooKassa\Model\PaymentMethodType;

/**
 * PaymentDataB2BSberbank
 * Платежные данные для проведения оплаты при помощи Сбербанк Бизнес Онлайн.
 * @property string $paymentPurpose Назначение платежа
 * @property VatData $vatData Данные об НДС
 */
class PaymentDataB2bSberbank extends AbstractPaymentData
{
    /**
     * @var string Назначение платежа
     */
    private $_paymentPurpose;

    /**
     * @var VatData Данные об НДС
     */
    private $_vatData;

    public function __construct()
    {
        $this->_setType(PaymentMethodType::B2B_SBERBANK);
    }

    /**
     * @return string Назначение платежа
     */
    public function getPaymentPurpose()
    {
        return $this->_paymentPurpose;
    }

    /**
     * @param string $value Назначение платежа
     */
    public function setPaymentPurpose($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty paymentPurpose value', 0,
                'PaymentDataB2bSberbank.paymentPurpose');
        } elseif (TypeCast::canCastToString($value)) {
            if (preg_match('/^.{1,210}$/', $value)) {
                $this->_paymentPurpose = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid paymentPurpose value', 0, 'PaymentDataB2bSberbank.paymentPurpose', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid paymentPurpose value type', 0, 'PaymentDataB2bSberbank.paymentPurpose', $value
            );
        }
    }

    /**
     * @return VatData Данные об НДС
     */
    public function getVatData()
    {
        return $this->_vatData;
    }

    /**
     * @param VatData|array|null $value Данные об НДС
     */
    public function setVatData($value)
    {
        if ($value === null || $value === array()) {
            $this->_vatData = null;
        } elseif ($value instanceof VatData) {
            $this->_vatData = $value;
        } elseif (is_array($value) || $value instanceof \Traversable) {
            $vatData = new VatData();
            foreach ($value as $property => $val) {
                $vatData->offsetSet($property, $val);
            }
            $this->_vatData = $vatData;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid vatData value type in PaymentDataB2BSberbank', 0,
                'PaymentDataB2BSberbank.vatData', $value
            );
        }
    }
}
