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

namespace YooKassa\Model\PaymentData\B2b\Sberbank;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\MonetaryAmount;

/**
 * Данные об НДС
 * @property string $type Способ расчёта НДС
 * @property string $rate Данные об НДС в случае, если сумма НДС включена в сумму платежа
 * @property AmountInterface $amount Сумма НДС
 */
class VatData extends AbstractObject implements VatDataInterface
{
    /**
     * @var string Способ расчёта НДС
     */
    private $_type;

    /**
     * @var string Налоговая ставка НДС
     */
    private $_rate;

    /**
     * @var AmountInterface Сумма НДС
     */
    private $_amount;

    /**
     * VatData constructor.
     * @param string|null $type Способ расчёта НДС
     * @param string|null $rate Налоговая ставка НДС
     * @param AmountInterface|null $amount Сумма НДС
     */
    public function __construct($type = null, $rate = null, $amount = null)
    {
        if ($type !== null) {
            $this->setType($type);
        }
        if ($rate !== null) {
            $this->setRate($rate);
        }
        if ($amount !== null) {
            $this->setAmount($amount);
        }
    }

    /**
     * @return string Способ расчёта НДС
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Устанавливает способ расчёта НДС
     * @param string $value Способ расчёта НДС
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданная строка не является валидным способом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setType($value)
    {
        if (TypeCast::canCastToEnumString($value)) {
            if (!VatDataType::valueExists((string)$value)) {
                throw new InvalidPropertyValueException('Invalid B2bSberbankVatData.type value', 0,
                    'B2bSberbankVatData.type', $value);
            }
            $this->_type = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid B2bSberbankVatData.type value type', 0, 'B2bSberbankVatData.type', $value
            );
        }
    }

    /**
     * @return string Налоговая ставка НДС
     */
    public function getRate()
    {
        return $this->_rate;
    }

    /**
     * Устанавливает налоговую ставку НДС
     * @param string $value Налоговая ставка НДС
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданная строка не является валидной ставкой
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setRate($value)
    {
        if (TypeCast::canCastToString($value)) {
            if (!VatDataRate::valueExists((string)$value)) {
                throw new InvalidPropertyValueException('Invalid B2bSberbankVatData.rate value', 0,
                    'B2bSberbankVatData.rate', $value);
            }
            $this->_rate = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid B2bSberbankVatData.rate value type', 0, 'B2bSberbankVatData.rate', $value
            );
        }
    }

    /**
     * Возвращает сумму НДС
     * @return AmountInterface Сумма НДС
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * Устанавливает сумму НДС
     * @param AmountInterface|array|null $value Сумма НДС
     */
    public function setAmount($value)
    {
        if ($value === null) {
            $this->_amount = null;
        } elseif ($value instanceof AmountInterface) {
            $this->_amount = $value;
        } elseif (is_array($value)) {
            $this->_amount = new MonetaryAmount();
            $this->_amount->fromArray($value);
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid B2bSberbankVatData.amount value type', 0, 'B2bSberbankVatData.amount', $value
            );
        }
    }

}
