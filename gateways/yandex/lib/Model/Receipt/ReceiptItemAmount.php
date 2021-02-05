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

namespace YooKassa\Model\Receipt;


use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\CurrencyCode;

/**
 * Class ReceiptItemAmount
 * @package YooKassa\Model\Receipt
 *
 * @method fromArray($sourceArray)
 */
class ReceiptItemAmount extends AbstractObject implements AmountInterface
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
     * @param string|null $value Сумма
     * @param string|null $currency Код валюты
     */
    public function __construct($value = null, $currency = null)
    {
        if ($value !== null && $value > 0.0) {
            $this->setValue($value);
        }
        if ($currency !== null) {
            $this->setCurrency($currency);
        }
    }

    /**
     * Возвращает значение суммы
     * @return string Сумма
     */
    public function getValue()
    {
        if ($this->_value < 10) {
            return '0.0' . $this->_value;
        } elseif ($this->_value < 100) {
            return '0.' . $this->_value;
        } else {
            return substr($this->_value, 0, -2) . '.' . substr($this->_value, -2);
        }
    }
    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty amount value', 0, 'amount.value');
        }
        if (!is_numeric($value)) {
            throw new InvalidPropertyValueTypeException('Invalid amount value type', 0, 'amount.value', $value);
        }
        if ($value < 0.0) {
            throw new InvalidPropertyValueException('Invalid amount value: "'.$value.'"', 0, 'amount.value', $value);
        }
        $castedValue = (int)round($value * 100.0);
        if ($castedValue < 0.0) {
            throw new InvalidPropertyValueException('Invalid amount value: "'.$value.'"', 0, 'amount.value', $value);
        }
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
     * Умножает текущую сумму на указанный коэффициент
     * @param float $coefficient Множитель
     *
     * @throws EmptyPropertyValueException Выбрасывается если передано пустое значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если было передано не число
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение меньше или равно нулю, либо если
     * после умножения получили значение равное нулю
     */
    public function multiply($coefficient)
    {
        if ($coefficient === null || $coefficient === '') {
            throw new EmptyPropertyValueException('Empty coefficient in multiply method', 0, 'amount.value');
        }
        if (!is_numeric($coefficient)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid coefficient type in multiply method', 0, 'amount.value', $coefficient
            );
        }
        if ($coefficient <= 0.0) {
            throw new InvalidPropertyValueException(
                'Invalid coefficient in multiply method: "' . $coefficient . '"', 0, 'amount.value', $coefficient
            );
        }
        $castedValue = (int)round($coefficient * $this->_value);
        if ($castedValue === 0) {
            throw new InvalidPropertyValueException(
                'Invalid coefficient value in multiply method: "' . $coefficient . '"', 0, 'amount.value', $coefficient
            );
        }
        $this->_value = $castedValue;
    }

    /**
     * Увеличивает сумму на указанное значение
     * @param int $value Значение которое будет прибавлено к текущему
     *
     * @throws EmptyPropertyValueException Выбрасывается если передано пустое значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если было передано не число
     * @throws InvalidPropertyValueException Выбрасывается если после сложения получилась сумма меньше или равная нулю
     */
    public function increase($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty amount value in increase method', 0, 'amount.value');
        }
        if (!is_numeric($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid amount value type in increase method', 0, 'amount.value', $value
            );
        }
        $castedValue = (int)round($this->_value + $value * 100.0);
        if ($castedValue <= 0) {
            throw new InvalidPropertyValueException(
                'Invalid amount value in increase method: "' . $value . '"', 0, 'amount.value', $value
            );
        }
        $this->_value = $castedValue;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'value' => sprintf('%.2f',$this->_value / 100.0),
            'currency' => $this->_currency,
        );
    }
}