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

namespace YooKassa\Model\Deal;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\CurrencyCode;

/**
 * MonetaryAmount - Сумма определенная в валюте
 *
 * @package YooKassa
 *
 * @property int $value Сумма
 * @property string $currency Код валюты
 */
class DealBalanceAmount extends AbstractObject implements AmountInterface
{
    /**
     * @var int Сумма
     */
    private $_value = 0;

    /**
     * @var string Код валюты
     */
    private $_currency = CurrencyCode::RUB;

    /**
     * MonetaryAmount constructor.
     * @param array|numeric|null $value Сумма
     * @param string|null $currency Код валюты
     */
    public function __construct($value = null, $currency = null)
    {
        if (is_array($value)) {
            parent::__construct($value);
        } else {
            if ($value !== null) {
                $this->setValue($value);
            }
            if ($currency !== null) {
                $this->setCurrency($currency);
            }
        }
    }

    /**
     * Возвращает значение суммы
     * @return string Сумма
     */
    public function getValue()
    {
        $negative = ($this->_value < 0 ? '-' : '');
        $mod = abs($this->_value);
        if ($mod < 10) {
            return $negative . '0.0' . $mod;
        } elseif ($mod < 100) {
            return $negative . '0.' . $mod;
        } else {
            return $negative . substr($mod, 0, -2) . '.' . substr($mod, -2);
        }
    }

    /**
     * Устанавливает сумму
     * @param string $value Сумма
     *
     * @throws EmptyPropertyValueException Генерируется если было передано пустое значение
     * @throws InvalidPropertyValueTypeException Генерируется если было передано значение невалидного типа
     * @throws InvalidPropertyValueException Генерируется если было передано не валидное значение
     */
    public function setValue($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty amount value', 0, 'amount.value');
        }
        if (!is_numeric($value)) {
            throw new InvalidPropertyValueTypeException('Invalid amount value type', 0, 'amount.value', $value);
        }
        $castedValue = (int)round($value * 100.0);
        $this->_value = $castedValue;
    }

    /**
     * Возвращает сумму в копейках в виде целого числа
     * @return int Сумма в копейках/центах
     */
    public function getIntegerValue()
    {
        return $this->_value;
    }

    /**
     * Возвращает валюту
     * @return string Код валюты
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * Устанавливает код валюты
     * @param string $value Код валюты
     *
     * @throws EmptyPropertyValueException Генерируется если было передано пустое значение
     * @throws InvalidPropertyValueTypeException Генерируется если было передано значение невалидного типа
     * @throws InvalidPropertyValueException Генерируется если был передан неподдерживаемый код валюты
     */
    public function setCurrency($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty currency value', 0, 'amount.currency');
        }
        if (TypeCast::canCastToEnumString($value)) {
            $value = strtoupper((string)$value);
            if (CurrencyCode::valueExists($value)) {
                $this->_currency = $value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid currency value: "' . $value . '"', 0, 'amount.currency', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException('Invalid currency value type', 0, 'amount.currency', $value);
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'value' => number_format($this->_value / 100.0, 2, '.', ''),
            'currency' => $this->_currency,
        );
    }
}
