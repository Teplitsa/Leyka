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
use YooKassa\Helpers\ProductCode;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\Receipt\AgentType;
use YooKassa\Model\Receipt\ReceiptItemAmount;

/**
 * Информация о товарной позиции в заказе, позиция фискального чека
 *
 * @property string $description Наименование товара
 * @property float $quantity Количество
 * @property-read float $amount Суммарная стоимость покупаемого товара в копейках/центах
 * @property AmountInterface $price Цена товара
 * @property Supplier $supplier Информация о поставщике товара или услуги
 * @property int $vatCode Ставка НДС, число 1-6
 * @property int $vat_code Ставка НДС, число 1-6
 * @property string $paymentSubject Признак предмета расчета
 * @property string $payment_subject Признак предмета расчета
 * @property string $paymentMode Признак способа расчета
 * @property string $payment_mode Признак способа расчета
 * @property string $productCode Код товара
 * @property string $product_code Код товара
 * @property string $countryOfOriginCode Код страны происхождения товара
 * @property string $country_of_origin_code Код страны происхождения товара
 * @property string $customsDeclarationNumber Номер таможенной декларации (от 1 до 32 символов)
 * @property string $customs_declaration_number Номер таможенной декларации (от 1 до 32 символов)
 * @property float $excise Сумма акциза товара с учетом копеек
 * @property-write bool $isShipping Флаг доставки
 */
class ReceiptItem extends AbstractObject implements ReceiptItemInterface
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
     * @var string Признак предмета расчета.
     */
    private $_paymentSubject;

    /**
     * @var string Признак способа расчета.
     */
    private $_paymentMode;

    /**
     * @var string Код товара.
     */
    private $_productCode;

    /**
     * @var string Код страны происхождения товара
     */
    private $_countryOfOriginCode;

    /**
     * @var string Номер таможенной декларации (от 1 до 32 символов).
     */
    private $_customsDeclarationNumber;

    /**
     * @var float Сумма акциза товара с учетом копеек. Десятичное число с точностью до 2 символов после точки.
     */
    private $_excise;

    /**
     * @var Supplier Информация о поставщике товара или услуги
     */
    private $_supplier;

    /**
     * @var string Тип посредника, реализующего товар или услугу
     */
    private $_agentType;

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
            $this->_description = mb_substr($castedValue, 0, 128);
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
     * @param AmountInterface|array $value Цена товара
     */
    public function setPrice($value)
    {
        if (is_array($value)) {
            $this->_amount = new ReceiptItemAmount($value);
        } elseif ($value instanceof AmountInterface) {
            $this->_amount = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid amount value type in ReceiptItem', 0, 'ReceiptItem.amount', $value
            );
        }
    }

    /**
     * Возвращает ставку НДС
     * @return int|null Ставка НДС, число 1-6, или null, если ставка не задана
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
     * Возвращает код товара — уникальный номер, который присваивается экземпляру товара при маркировке
     * @return string|null Код товара
     */
    public function getProductCode()
    {
        return $this->_productCode;
    }

    /**
     * Устанавливает код товара — уникальный номер, который присваивается экземпляру товара при маркировке
     *
     * @param string|ProductCode $value Код товара
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве аргумента была передана не строка
     */
    public function setProductCode($value)
    {
        if ($value instanceof ProductCode) {
            $value = (string)$value;
        }
        if ($value === null || $value === '') {
            $this->_productCode = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid productCode value type', 0, 'ReceiptItem.productCode', $value
            );
        } elseif (strlen((string)$value) > 96) {
            throw new InvalidPropertyValueException(
                'Invalid productCode value: "'.$value.'"', 0, 'ReceiptItem.productCode', $value
            );
        } elseif (!preg_match('/^[0-9A-F ]{2,96}$/', (string)$value)) {
            throw new InvalidPropertyValueException(
                'Invalid productCode value: "'.$value.'"', 0, 'ReceiptItem.productCode', $value
            );
        } else {
            $this->_productCode = $value;
        }
    }

    /**
     * Возвращает код страны происхождения товара по общероссийскому классификатору стран мира
     * @return string|null Код страны происхождения товара
     */
    public function getCountryOfOriginCode()
    {
        return $this->_countryOfOriginCode;
    }

    /**
     * Устанавливает код страны происхождения товара по общероссийскому классификатору стран мира
     *
     * @param string $value Код страны происхождения товара
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве аргумента была передана не строка
     */
    public function setCountryOfOriginCode($value)
    {
        if ($value === null || $value === '') {
            $this->_countryOfOriginCode = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid countryOfOriginCode value type', 0, 'ReceiptItem.countryOfOriginCode', $value
            );
        } elseif (strlen((string)$value) != 2) {
            throw new InvalidPropertyValueException(
                'Invalid countryOfOriginCode value: "'.$value.'"', 0, 'ReceiptItem.countryOfOriginCode', $value
            );
        } elseif (!preg_match('/^[A-Z]{2}$/', (string)$value)) {
            throw new InvalidPropertyValueException(
                'Invalid countryOfOriginCode value: "'.$value.'"', 0, 'ReceiptItem.countryOfOriginCode', $value
            );
        } else {
            $this->_countryOfOriginCode = $value;
        }
    }

    /**
     * Возвращает номер таможенной декларации
     * @return string|null Номер таможенной декларации (от 1 до 32 символов)
     */
    public function getCustomsDeclarationNumber()
    {
        return $this->_customsDeclarationNumber;
    }

    /**
     * Устанавливает номер таможенной декларации (от 1 до 32 символов)
     *
     * @param string $value Номер таможенной декларации
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве аргумента была передана не строка
     */
    public function setCustomsDeclarationNumber($value)
    {
        if ($value === null || $value === '') {
            $this->_customsDeclarationNumber = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid customsDeclarationNumber value type', 0, 'ReceiptItem.customsDeclarationNumber', $value
            );
        } elseif (strlen((string)$value) > 32) {
            throw new InvalidPropertyValueException(
                'Invalid customsDeclarationNumber value: "'.$value.'"', 0, 'ReceiptItem.customsDeclarationNumber', $value
            );
        } else {
            $this->_customsDeclarationNumber = $value;
        }
    }

    /**
     * Возвращает сумму акциза товара с учетом копеек
     * @return float|null Сумма акциза товара с учетом копеек
     */
    public function getExcise()
    {
        return $this->_excise;
    }

    /**
     * Устанавливает сумму акциза товара с учетом копеек
     *
     * @param float $value Сумма акциза товара с учетом копеек
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве аргумента было передано не число
     */
    public function setExcise($value)
    {
        if ($value === null || $value === '') {
            $this->_excise = null;
        } elseif (!is_numeric($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid excise value type', 0, 'ReceiptItem.excise', $value
            );
        } elseif ($value <= 0.0) {
            throw new InvalidPropertyValueException(
                'Invalid excise value in ReceiptItem', 0, 'ReceiptItem.excise', $value
            );
        } else {
            $this->_excise = $value;
        }
    }

    /**
     * Устанавливает флаг доставки для текущего объекта айтема в чеке
     *
     * @param bool $value True если айтем является доставкой, false если нет
     *
     * @return ReceiptItem
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

        return $this;
    }

    /**
     * Возвращает информацию о поставщике товара или услуги.
     *
     * @return Supplier
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
     * Устанавливает тип посредника, реализующего товар или услугу
     * @param string $value Тип посредника
     */
    public function setAgentType($value)
    {
        if ($value === null || $value === '') {
            $this->_paymentMode = null;
        } elseif (!TypeCast::canCastToEnumString($value)) {
            throw new InvalidPropertyValueException(
                'Invalid value for "agentType" parameter in Receipt.item.agentType',
                0,
                'Receipt.item.agentType',
                $value
            );
        } elseif (!AgentType::valueExists($value)) {
            throw new InvalidPropertyValueException(
                'Invalid value for "agentType" parameter in Receipt.item.agentType',
                0,
                'Receipt.item.agentType',
                $value
            );
        }

        $this->_agentType = $value;
    }

    /**
     * Возвращает тип посредника, реализующего товар или услугу
     *
     * @return string Тип посредника
     */
    public function getAgentType()
    {
        return $this->_agentType;
    }


    /**
     * Проверяет, является ли текущий элемент чека доставкой
     *
     * @return bool True если доставка, false если обычный товар
     */
    public function isShipping()
    {
        return $this->_shipping;
    }

    /**
     * Применяет для товара скидку
     *
     * @param float $coefficient Множитель скидки
     */
    public function applyDiscountCoefficient($coefficient)
    {
        $this->_amount->multiply($coefficient);
    }

    /**
     * Увеличивает цену товара на указанную величину
     *
     * @param float $value Сумма на которую цену товара увеличиваем
     */
    public function increasePrice($value)
    {
        $this->_amount->increase($value);
    }

    /**
     * Уменьшает количество покупаемого товара на указанное, возвращает объект позиции в чеке с уменьшаемым количеством
     *
     * @param float $count Количество на которое уменьшаем позицию в чеке
     *
     * @return ReceiptItem
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

        $result = clone $this;
        $result->setPrice(clone $this->getPrice());
        $result->setQuantity($count);
        $this->_quantity -= $count;

        return $result;
    }

    /**
     * Устанавливает значения свойств текущего объекта из массива
     *
     * @param array|\Traversable $sourceArray Ассоциативный массив с настройками
     */
    public function fromArray($sourceArray)
    {
        if (isset($sourceArray['amount'])) {
            if (is_array($sourceArray['amount'])) {
                $sourceArray['price'] = new ReceiptItemAmount($sourceArray['amount']);
            } elseif ($sourceArray['amount'] instanceof AmountInterface) {
                $sourceArray['price'] = $sourceArray['amount'];
            }
            unset($sourceArray['amount']);
        }

        parent::fromArray($sourceArray);
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        $result['amount'] = $result['price'];
        unset($result['price']);

        return $result;
    }
}
