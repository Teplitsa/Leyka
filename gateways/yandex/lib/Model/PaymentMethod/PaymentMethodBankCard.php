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

namespace YandexCheckout\Model\PaymentMethod;

use YandexCheckout\Common\Exceptions\EmptyPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException;
use YandexCheckout\Helpers\TypeCast;
use YandexCheckout\Model\PaymentMethodType;

/**
 * PaymentMethodBankCard
 * Объект, описывающий метод оплаты банковской картой
 * @property string $type Тип объекта
 * @property string $last4 Последние 4 цифры номера карты
 * @property string $first6 Первые 6 цифр номера карты
 * @property string $expiryYear Срок действия, год
 * @property string $expiry_year Срок действия, год
 * @property string $expiryMonth Срок действия, месяц
 * @property string $expiry_month Срок действия, месяц
 * @property string $cardType Тип банковской карты
 * @property string $card_type Тип банковской карты
 */
class PaymentMethodBankCard extends AbstractPaymentMethod
{
    /**
     * @var string Последние 4 цифры номера карты
     */
    private $_last4;

    /**
     * @var string Первые 6 цифр номера карты
     */
    private $_first6;

    /**
     * @var string Срок действия, год
     */
    private $_expiryYear;

    /**
     * @var string Срок действия, месяц
     */
    private $_expiryMonth;

    /**
     * @var string Тип банковской карты
     */
    private $_cardType;

    public function __construct()
    {
        $this->_setType(PaymentMethodType::BANK_CARD);
    }

    /**
     * @return string Последние 4 цифры номера карты
     */
    public function getLast4()
    {
        return $this->_last4;
    }

    /**
     * @param string $value Последние 4 цифры номера карты
     */
    public function setLast4($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty card last4 value', 0, 'PaymentMethodBankCard.last4');
        } elseif (TypeCast::canCastToString($value)) {
            if (preg_match('/^[0-9]{4}$/', (string)$value)) {
                $this->_last4 = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid card last4 value', 0, 'PaymentMethodBankCard.last4', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid card last4 value type', 0, 'PaymentMethodBankCard.last4', $value
            );
        }
    }

    /**
     * @return string
     * @since 1.0.14
     */
    public function getFirst6()
    {
        return $this->_first6;
    }

    /**
     * @param $value
     * @since 1.0.14
     */
    public function setFirst6($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty card first6 value', 0, 'PaymentMethodBankCard.first6');
        } elseif (TypeCast::canCastToString($value)) {
            if (preg_match('/^[0-9]{6}$/', (string)$value)) {
                $this->_first6 = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid card first6 value', 0, 'PaymentMethodBankCard.first6', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid card first6 value type', 0, 'PaymentMethodBankCard.first6', $value
            );
        }
    }

    /**
     * @return string Срок действия, год
     */
    public function getExpiryYear()
    {
        return $this->_expiryYear;
    }

    /**
     * @param string $value Срок действия, год
     */
    public function setExpiryYear($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty card expiry year value', 0, 'PaymentMethodBankCard.expiryYear'
            );
        } elseif (is_numeric($value)) {
            if (!preg_match('/^\d\d\d\d$/', $value) || $value < 2000 || $value > 2200) {
                throw new InvalidPropertyValueException(
                    'Invalid card expiry year value', 0, 'PaymentMethodBankCard.expiryYear', $value
                );
            }
            $this->_expiryYear = (string)$value;
        } else {
            throw new InvalidPropertyValueException(
                'Invalid card expiry year value', 0, 'PaymentMethodBankCard.expiryYear', $value
            );
        }
    }

    /**
     * @return string Срок действия, месяц
     */
    public function getExpiryMonth()
    {
        return $this->_expiryMonth;
    }

    /**
     * @param string $value Срок действия, месяц
     */
    public function setExpiryMonth($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty card expiry month value', 0, 'PaymentMethodBankCard.expiryMonth'
            );
        } elseif (is_numeric($value)) {
            if (!preg_match('/^\d\d$/', $value)) {
                throw new InvalidPropertyValueException(
                    'Invalid card expiry month value', 0, 'PaymentMethodBankCard.expiryMonth', $value
                );
            }
            if (is_string($value) && $value[0] == '0') {
                $month = (int)($value[1]);
            } else {
                $month = (int)$value;
            }
            if ($month < 1 || $month > 12) {
                throw new InvalidPropertyValueException(
                    'Invalid card expiry month value', 0, 'PaymentMethodBankCard.expiryMonth', $value
                );
            } else {
                $this->_expiryMonth = (string)$value;
            }
        } else {
            throw new InvalidPropertyValueException(
                'Invalid card expiry month value', 0, 'PaymentMethodBankCard.expiryMonth', $value
            );
        }
    }

    /**
     * @return string Тип банковской карты
     */
    public function getCardType()
    {
        return $this->_cardType;
    }

    /**
     * @param string $value Тип банковской карты
     */
    public function setCardType($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty cardType value', 0, 'PaymentMethodBankCard.cardType');
        } elseif (TypeCast::canCastToString($value)) {
            $castedValue = (string)$value;
            if (PaymentMethodCardType::valueExists($castedValue)) {
                $this->_cardType = $castedValue;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid cardType value', 0, 'PaymentMethodBankCard.cardType', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid cardType value type', 0, 'PaymentMethodBankCard.cardType', $value
            );
        }
    }
}
