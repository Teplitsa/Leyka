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

namespace YandexCheckout\Model;

use YandexCheckout\Common\AbstractObject;
use YandexCheckout\Common\Exceptions\EmptyPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException;
use YandexCheckout\Helpers\TypeCast;

/**
 * Информация о товарной позиции в заказе, позиция фискального чека
 *
 * @property string $description Наименование товара
 * @property int $quantity Количество
 * @property-read int $amount Суммарная стоимость покупаемого товара в копейках/центах
 * @property AmountInterface $price Цена товара
 * @property int $vatCode Ставка НДС, число 1-6
 * @property int $vat_code Ставка НДС, число 1-6
 * @property-write bool $isShipping Флаг доставки
 */
class ReceiptItem extends AbstractObject implements ReceiptItemInterface
{
    /**
     * @var string Наименование товара
     */
    private $_description;

    /**
     * @var int Количество
     */
    private $_quantity;

    /**
     * @var MonetaryAmount Цена товара
     */
    private $_amount;

    /**
     * @var int Ставка НДС, число 1-6
     */
    private $_vatCode;

    /**
     * @var bool True если текущий айтем доставка, false если нет
     */
    private $_shipping = false;

    /**
     * Возвращает наименование товара
     * @return string Наименование товара
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Устанавливает наименование товара
     * @param string $value Наименование товара
     *
     * @throws EmptyPropertyValueException Выбрасывается если было передано пустое значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве аргумента была передана не строка
     */
    public function setDescription($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty description value in ReceiptItem', 0, 'ReceiptItem.description'
            );
        } elseif (TypeCast::canCastToString($value)) {
            $castedValue = (string)$value;
            if ($castedValue === '') {
                throw new EmptyPropertyValueException(
                    'Empty description value in ReceiptItem', 0, 'ReceiptItem.description'
                );
            }
            $this->_description = $castedValue;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Empty description value in ReceiptItem', 0, 'ReceiptItem.description', $value
            );
        }
    }

    /**
     * Возвращает количество товара
     * @return float Количество купленного товара
     */
    public function getQuantity()
    {
        return $this->_quantity;
    }

    /**
     * Устанавливает количество покупаемого товара
     * @param int $value Количество
     *
     * @throws EmptyPropertyValueException Выбрасывается если было передано пустое значение
     * @throws InvalidPropertyValueException Выбрасывается если в качестве аргумента был передан ноль
     * или отрицательное число
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве аргумента было передано не число
     */
    public function setQuantity($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty quantity value in ReceiptItem', 0, 'ReceiptItem.quantity');
        } elseif (!is_numeric($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid quantity value type in ReceiptItem', 0, 'ReceiptItem.quantity', $value
            );
        } elseif ($value <= 0.0) {
            throw new InvalidPropertyValueException(
                'Invalid quantity value in ReceiptItem', 0, 'ReceiptItem.quantity', $value
            );
        } else {
            $this->_quantity = (float)$value;
        }
    }

    /**
     * Возвращает общую стоимость покупаемого товара в копейках/центах
     * @return int Сумма стоимости покупаемого товара
     */
    public function getAmount()
    {
        return (int)round($this->_amount->getIntegerValue() * $this->_quantity);
    }

    /**
     * Возвращает цену товара
     * @return AmountInterface Цена товара
     */
    public function getPrice()
    {
        return $this->_amount;
    }

    /**
     * Устанавливает цену товара
     * @param AmountInterface $value Цена товара
     */
    public function setPrice(AmountInterface $value)
    {
        $this->_amount = $value;
    }

    /**
     * Возвращает ставку НДС
     * @return int|null Ставка НДС, число 1-6, или null если ставка не задана
     */
    public function getVatCode()
    {
        return $this->_vatCode;
    }

    /**
     * Устанавливает ставку НДС
     * @param int $value Ставка НДС, число 1-6
     *
     * @throws InvalidPropertyValueException Выбрасывается если в качестве аргумента было передано число меньше одного
     * или больше шести
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве аргумента было передано не число
     */
    public function setVatCode($value)
    {
        if ($value === null || $value === '') {
            $this->_vatCode = null;
        } elseif (!is_numeric($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid vatId value type in ReceiptItem', 0, 'ReceiptItem.vatId', $value
            );
        } elseif ($value < 1 || $value > 6) {
            throw new InvalidPropertyValueException(
                'Invalid vatId value in ReceiptItem', 0, 'ReceiptItem.vatId', $value
            );
        } else {
            $this->_vatCode = (int)$value;
        }
    }

    /**
     * Устанавливает флаг доставки для текущего объекта айтема в чеке
     * @param bool $value True если айтем является доставкой, false если нет
     *
     * @throws InvalidPropertyValueException Генерируется если передано значение невалидного типа
     */
    public function setIsShipping($value)
    {
        if ($value === null || $value === '') {
            $this->_shipping = false;
        } elseif (TypeCast::canCastToBoolean($value)) {
            $this->_shipping = $value ? true : false;
        } else {
            throw new InvalidPropertyValueException(
                'Invalid isShipping value in ReceiptItem', 0, 'ReceiptItem.isShipping', $value
            );
        }
    }

    /**
     * Проверяет, является ли текущий элемент чека доствкой
     * @return bool True если доставка, false если обычный товар
     */
    public function isShipping()
    {
        return $this->_shipping;
    }

    /**
     * Применяет для товара скидку
     * @param float $coefficient Множитель скидки
     */
    public function applyDiscountCoefficient($coefficient)
    {
        $this->_amount->multiply($coefficient);
    }

    /**
     * Увеличивает цену товара на указанную величину
     * @param float $value Сумма на которую цену товара увеличиваем
     */
    public function increasePrice($value)
    {
        $this->_amount->increase($value);
    }

    /**
     * Уменьшает количество покупаемого товара на указанное, возвращает объект позиции в чеке с уменьшаемым количеством
     * @param float $count Количество на которое уменьшаем позицию в чеке
     * @return ReceiptItem Новый инстанс позиции в чеке
     *
     * @throws EmptyPropertyValueException Выбрасывается если было передано пустое значение
     * @throws InvalidPropertyValueException Выбрасывается если в качестве аргумента был передан ноль
     * или отрицательное число, или число больше текущего количества покупаемого товара
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве аргумента было передано не число
     */
    public function fetchItem($count)
    {
        if ($count === null || $count === '') {
            throw new EmptyPropertyValueException(
                'Empty quantity value in ReceiptItem in fetchItem method', 0, 'ReceiptItem.quantity'
            );
        } elseif (!is_numeric($count)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid quantity value type in ReceiptItem in fetchItem method', 0, 'ReceiptItem.quantity', $count
            );
        } elseif ($count <= 0.0 || $count >= $this->_quantity) {
            throw new InvalidPropertyValueException(
                'Invalid quantity value in ReceiptItem in fetchItem method', 0, 'ReceiptItem.quantity', $count
            );
        }
        $result = new ReceiptItem();
        $result->_description = $this->_description;
        $result->_quantity = $count;
        $result->_vatCode = $this->_vatCode;
        $result->_amount = new MonetaryAmount(
            $this->_amount->getValue(),
            $this->_amount->getCurrency()
        );
        $this->_quantity -= $count;
        return $result;
    }
}
