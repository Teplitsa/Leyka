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

namespace YooKassa\Model\Payout;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Данные банковской карты
 * Необходим при оплате PCI-DSS данными.
 * @property string $last4 Последние 4 цифры номера карты
 * @property string $first6 Первые 6 цифр номера карты
 * @property string $cardType Тип банковской карты
 * @property string $card_type Тип банковской карты
 * @property string $issuerCountry Код страны, в которой выпущена карта
 * @property string $issuer_country Код страны, в которой выпущена карта
 * @property string $issuerName Тип банковской карты
 * @property string $issuer_name Тип банковской карты
 */
class PayoutDestinationBankCardCard extends AbstractObject
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
     * Возвращает последние 4 цифры номера карты
     * @return string Последние 4 цифры номера карты
     */
    public function getLast4()
    {
        return $this->_last4;
    }

    /**
     * Устанавливает последние 4 цифры номера карты
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
     * Возвращает первые 6 цифр номера карты
     * @return string Первые 6 цифр номера карты
     * @since 1.0.14
     */
    public function getFirst6()
    {
        return $this->_first6;
    }

    /**
     * Устанавливает первые 6 цифр номера карты
     * @param string $value Первые 6 цифр номера карты
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
     * Возвращает тип банковской карты
     * @return string Тип банковской карты
     */
    public function getCardType()
    {
        return $this->_cardType;
    }

    /**
     * Устанавливает тип банковской карты
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
     * Возвращает код страны, в которой выпущена карта. Передается в формате ISO-3166 alpha-2
     * @return string Код страны, в которой выпущена карта
     */
    public function getIssuerCountry()
    {
        return $this->_issuerCountry;
    }

    /**
     * Устанавливает код страны, в которой выпущена карта. Передается в формате ISO-3166 alpha-2
     * @param string $value Код страны, в которой выпущена карта
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
     * Устанавливает наименование банка, выпустившего карту
     * @param string $value Наименование банка, выпустившего карту
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
     * Возвращает наименование банка, выпустившего карту
     * @return string Наименование банка, выпустившего карту.
     */
    public function getIssuerName()
    {
        return $this->_issuerName;
    }

}
