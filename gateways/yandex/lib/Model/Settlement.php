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

namespace YooKassa\Model;


use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\Receipt\SettlementType;

/**
 * Class Settlement
 * @package YooKassa
 *
 * @property string $type Вид оплаты в чеке
 * @property AmountInterface $amount Размер оплаты
 */
class Settlement extends AbstractObject implements SettlementInterface
{
    /**
     * @var string Вид оплаты в чеке (cashless | prepayment | postpayment | consideration)
     */
    private $_type;

    /**
     * @var AmountInterface Размер оплаты
     */
    private $_amount;

    /**
     * Возвращает вид оплаты в чеке (cashless | prepayment | postpayment | consideration)
     * @return string Вид оплаты в чеке
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Устанавливает вид оплаты в чеке
     * @param string $value
     */
    public function setType($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for "type" parameter in Settlement', 0, 'settlement.type'
            );
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (SettlementType::valueExists($value)) {
                $this->_type = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid value for "type" parameter in Settlement', 0, 'settlement.type', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "type" parameter in Settlement', 0, 'settlement.type', $value
            );
        }
    }

    /**
     * Возвращает размер оплаты
     * @return AmountInterface Размер оплаты
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * Устанавливает сумму платежа
     * @param AmountInterface|array $value Сумма платежа
     */
    public function setAmount($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty value for "amount" parameter in Settlement', 0, 'settlement.amount'
            );
        } elseif (is_array($value)) {
            $this->_amount = $this->factoryAmount($value);
        } elseif ($value instanceof AmountInterface) {
            $this->_amount = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "amount" parameter in Settlement', 0, 'settlement.amount', $value
            );
        }
    }

    /**
     * Фабричный метод создания суммы
     *
     * @param array $options Сумма в виде ассоциативного массива
     *
     * @return AmountInterface Созданный инстанс суммы
     */
    private function factoryAmount($options)
    {
        $amount = new MonetaryAmount(null, $options['currency']);
        if ($options['value'] > 0) {
            $amount->setValue($options['value']);
        }

        return $amount;
    }
}
