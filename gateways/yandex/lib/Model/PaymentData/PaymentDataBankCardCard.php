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

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Данные банковской карты
 * Необходим при оплате PCI-DSS данными.
 * @property string $number Номер банковской карты
 * @property string $expiryYear Срок действия, год, YY
 * @property string $expiry_year Срок действия, год, YY
 * @property string $expiryMonth Срок действия, месяц, MM
 * @property string $expiry_month Срок действия, месяц, MM
 * @property string $csc CVV2/CVC2 код
 * @property string $cardholder Имя держателя карты
 */
class PaymentDataBankCardCard extends AbstractObject
{
    /**
     * @var string Номер банковской карты
     */
    private $_number;

    /**
     * @var string Срок действия, год, YY
     */
    private $_expiryYear;

    /**
     * @var string Срок действия, месяц, MM
     */
    private $_expiryMonth;

    /**
     * @var string CVV2/CVC2 код
     */
    private $_csc;

    /**
     * @var string Имя держателя карты
     */
    private $_cardholder;

    /**
     * @return string Номер банковской карты
     */
    public function getNumber()
    {
        return $this->_number;
    }

    /**
     * @param string $value Номер банковской карты
     */
    public function setNumber($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty card number value', 0, 'PaymentDataBankCardCard.number');
        } elseif (TypeCast::canCastToString($value)) {
            if (preg_match('/^[0-9]{16,19}$/', (string)$value)) {
                $this->_number = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid card number value', 0, 'PaymentDataBankCardCard.number', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid card number value type', 0, 'PaymentDataBankCardCard.number', $value
            );
        }
    }

    /**
     * @return string Срок действия, год, YYYY
     */
    public function getExpiryYear()
    {
        return $this->_expiryYear;
    }

    /**
     * @param string $value Срок действия, год, YYYY
     */
    public function setExpiryYear($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty card expiry year value', 0, 'PaymentDataBankCardCard.expiryYear'
            );
        } elseif (is_numeric($value)) {
            if (!preg_match('/^\d\d\d\d$/', $value) || $value < 2000 || $value > 2200) {
                throw new InvalidPropertyValueException(
                    'Invalid card expiry year value', 0, 'PaymentDataBankCardCard.expiryYear', $value
                );
            }
            $this->_expiryYear = (string)$value;
        } else {
            throw new InvalidPropertyValueException(
                'Invalid card expiry year value', 0, 'PaymentDataBankCardCard.expiryYear', $value
            );
        }
    }

    /**
     * @return string Срок действия, месяц, MM
     */
    public function getExpiryMonth()
    {
        return $this->_expiryMonth;
    }

    /**
     * @param string $value Срок действия, месяц, MM
     */
    public function setExpiryMonth($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty card expiry month value', 0, 'PaymentDataBankCardCard.expiryMonth'
            );
        } elseif (is_numeric($value)) {
            if (!preg_match('/^\d\d$/', $value)) {
                throw new InvalidPropertyValueException(
                    'Invalid card expiry month value', 0, 'PaymentDataBankCardCard.expiryMonth', $value
                );
            }
            if (is_string($value) && $value[0] == '0') {
                $month = (int)($value[1]);
            } else {
                $month = (int)$value;
            }
            if ($month < 1 || $month > 12) {
                throw new InvalidPropertyValueException(
                    'Invalid card expiry month value', 0, 'PaymentDataBankCardCard.expiryMonth', $value
                );
            } else {
                $this->_expiryMonth = (string)$value;
            }
        } else {
            throw new InvalidPropertyValueException(
                'Invalid card expiry month value', 0, 'PaymentDataBankCardCard.expiryMonth', $value
            );
        }
    }

    /**
     * @return string CVV2/CVC2 код
     */
    public function getCsc()
    {
        return $this->_csc;
    }

    /**
     * @param string $value CVV2/CVC2 код
     */
    public function setCsc($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty card CSC code value', 0, 'PaymentDataBankCardCard.csc'
            );
        } elseif (is_numeric($value)) {
            if (preg_match('/^\d{3,4}$/', $value)) {
                $this->_csc = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid card CSC code value', 0, 'PaymentDataBankCardCard.csc', $value
                );
            }
        } else {
            throw new InvalidPropertyValueException(
                'Invalid card CSC code value', 0, 'PaymentDataBankCardCard.csc', $value
            );
        }
    }

    /**
     * @return string Имя держателя карты
     */
    public function getCardholder()
    {
        return $this->_cardholder;
    }

    /**
     * @param string $value Имя держателя карты
     */
    public function setCardholder($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty card holder value', 0, 'PaymentDataBankCardCard.cardholder'
            );
        } elseif (TypeCast::canCastToString($value)) {
            if (preg_match('/^[a-zA-Z\s]{1,26}$/', $value)) {
                $this->_cardholder = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid card holder value', 0, 'PaymentDataBankCardCard.cardholder', $value
                );
            }
        } else {
            throw new InvalidPropertyValueException(
                'Invalid card holder value', 0, 'PaymentDataBankCardCard.cardholder', $value
            );
        }
    }
}
