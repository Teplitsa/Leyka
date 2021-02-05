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
 * @property string $issuerCountry Тип банковской карты
 * @property string $issuer_country Тип банковской карты
 * @property string issuerName Тип банковской карты
 * @property string $issuer_name Тип банковской карты
 * @property string $source Тип банковской карты
 */
class PaymentMethodBankCard extends AbstractPaymentMethod
{
    /**
     * @var string Длина кода страны по ISO 3166 https://www.iso.org/obp/ui/#iso:pub:PUB500001:en
     */
    const ISO_3166_CODE_LENGTH = 2;

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

    /**
     * @var string Код страны, в которой выпущена карта
     */
    private $_issuerCountry;

    /**
     * @var string Наименование банка, выпустившего карту
     */
    private $_issuerName;

    /**
     * @var string Источник данных банковской карты
     */
    private $_source;

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
            $this->_cardType = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid cardType value type', 0, 'PaymentMethodBankCard.cardType', $value
            );
        }
    }

    /**
     * @return string
     */
    public function getIssuerCountry()
    {
        return $this->_issuerCountry;
    }

    /**
     * @param string $value
     */
    public function setIssuerCountry($value)
    {
        if ($value === null || $value === '') {
            $this->_issuerCountry = (string)$value;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid issuerCountry value type', 0, 'PaymentMethodBankCard.issuerCountry', $value
            );
        } elseif (strlen($value) !== self::ISO_3166_CODE_LENGTH) {
            throw new InvalidPropertyValueException(
                'Invalid issuerCountry value', 0, 'PaymentMethodBankCard.issuerCountry', $value
            );
        }

        $this->_issuerCountry = (string)$value;
    }

    /**
     * @param string $value
     */
    public function setIssuerName($value)
    {
        if ($value === null || $value === '') {
            $this->_issuerName = (string)$value;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new EmptyPropertyValueException(
                'Empty issuerName value', 0, 'PaymentMethodBankCard.issuerName'
            );
        }

        $this->_issuerName = (string)$value;
    }

    /**
     * @return string
     */
    public function getIssuerName()
    {
        return $this->_issuerName;
    }

    /**
     * @param string $value
     */
    public function setSource($value)
    {
        if ($value === null || $value === '') {
            $this->_source = (string)$value;
        } elseif (!TypeCast::canCastToEnumString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid source value type', 0, 'PaymentMethodBankCard.source', $value
            );
        } elseif (!BankCardSource::valueExists($value)) {
            throw new InvalidPropertyValueException(
                'Invalid source value', 0, 'PaymentMethodBankCard.source', $value
            );
        }

        $this->_source = (string)$value;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->_source;
    }
}
