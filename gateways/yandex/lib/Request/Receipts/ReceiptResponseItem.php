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

namespace YooKassa\Request\Receipts;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\MonetaryAmount;
use YooKassa\Model\Receipt\ReceiptItemAmount;
use YooKassa\Model\Supplier;
use YooKassa\Model\SupplierInterface;

/**
 * Interface ReceiptItemInterface
 *
 * @package YooKassa\Model
 *
 * @property string $description Название товара (не более 128 символов).
 * @property float $quantity Количество товара. Максимально возможное значение зависит от модели вашей онлайн-кассы.
 * @property float $amount Суммарная стоимость товара в копейках/центах
 * @property AmountInterface $price Цена товара
 * @property int $vatCode Ставка НДС, число 1-6
 * @property int $vat_code Ставка НДС, число 1-6
 * @property string $paymentSubject Признак предмета расчета
 * @property string $payment_subject Признак предмета расчета
 * @property string $paymentMode Признак способа расчета
 * @property string $payment_mode Признак способа расчета
 */
class ReceiptResponseItem extends AbstractObject implements ReceiptResponseItemInterface
{
    /**
     * @var string Наименование товара
     */
    private $_description;

    /**
     * @var float Количество
     */
    private $_quantity;

    /**
     * @var ReceiptItemAmount Цена товара
     */
    private $_amount;

    /**
     * @var int Ставка НДС, число 1-6
     */
    private $_vatCode;

    /**
     * @var string Признак предмета расчета
     */
    private $_paymentSubject;

    /**
     * @var string Признак способа расчета
     */
    private $_paymentMode;

    /**
     * @var SupplierInterface Информация о поставщике товара или услуги
     */
    private $_supplier;

    /**
     * Конструктор, устанавливает настройки товара из ассоциативного массива
     *
     * @param array $itemData Массив с информацией о товаре, пришедший от API
     */
    public function __construct($itemData)
    {
        $this->setDescription($itemData['description']);
        $this->setQuantity($itemData['quantity']);
        $this->setPrice($this->factoryAmount($itemData['amount']));
        $this->setVatCode($itemData['vat_code']);

        if (!empty($itemData['payment_mode'])) {
            $this->setPaymentMode($itemData['payment_mode']);
        }

        if (!empty($itemData['payment_subject'])) {
            $this->setPaymentSubject($itemData['payment_subject']);
        }

        if (!empty($itemData['supplier'])) {
            $this->setSupplier($itemData['supplier']);
        }
    }

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
     *
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
     *
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
     *
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
     *
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
     * Возвращает признак предмета расчета
     * @return string|null Признак предмета расчета
     */
    public function getPaymentSubject()
    {
        return $this->_paymentSubject;
    }

    /**
     * Устанавливает признак предмета расчета
     *
     * @param string $value Признак предмета расчета
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве аргумента была передана не строка
     */
    public function setPaymentSubject($value)
    {
        if ($value === null || $value === '') {
            $this->_paymentSubject = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException('Invalid paymentSubject value type', 0, 'ReceiptItem.paymentSubject');
        } else {
            $this->_paymentSubject = $value;
        }
    }

    /**
     * Возвращает признак способа расчета
     * @return string|null Признак способа расчета
     */
    public function getPaymentMode()
    {
        return $this->_paymentMode;
    }

    /**
     * Устанавливает признак способа расчета
     *
     * @param string $value Признак способа расчета
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве аргумента была передана не строка
     */
    public function setPaymentMode($value)
    {
        if ($value === null || $value === '') {
            $this->_paymentMode = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid paymentMode value type', 0, 'ReceiptItem.paymentMode', $value
            );
        } else {
            $this->_paymentMode = $value;
        }
    }

    /**
     * @return SupplierInterface
     */
    public function getSupplier()
    {
        return $this->_supplier;
    }

    /**
     * Устанавливает информацию о поставщике товара или услуги.
     *
     * @param SupplierInterface|array $value
     */
    public function setSupplier($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty supplier value in receipt', 0, 'Receipt.supplier'
            );
        }

        if (is_array($value)) {
            $value = new Supplier($value);
        }

        if (!($value instanceof SupplierInterface)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid supplier value type in receipt', 0, 'Receipt.supplier', $value
            );
        }

        $this->_supplier = $value;
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

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = array(
            'description'     => $this->getDescription(),
            'amount'          => array(
                'value'    => $this->getPrice()->getValue(),
                'currency' => $this->getPrice()->getCurrency(),
            ),
            'quantity'        => $this->getQuantity(),
            'vat_code'        => $this->getVatCode(),
        );

        if ($this->getPaymentSubject()) {
            $result['payment_subject'] = $this->getPaymentSubject();
        }

        if ($this->getPaymentMode()) {
            $result['payment_mode'] = $this->getPaymentMode();
        }

        return $result;
    }
}