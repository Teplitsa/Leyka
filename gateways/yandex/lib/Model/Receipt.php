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

/**
 * Класс данных для формирования чека в онлайн-кассе (для соблюдения 54-ФЗ)
 *
 * @property ReceiptCustomer $customer Информация о плательщике
 * @property ReceiptItemInterface[] $items Список товаров в заказе
 * @property SettlementInterface[] $settlements Массив оплат, обеспечивающих выдачу товара
 * @property int $taxSystemCode Код системы налогообложения. Число 1-6.
 * @property int $tax_system_code Код системы налогообложения. Число 1-6.
 */
class Receipt extends AbstractObject implements ReceiptInterface
{
    /**
     * @var ReceiptCustomer Информация о плательщике
     */
    private $_customer;

    /**
     * @var ReceiptItem[] Список товаров в заказе
     */
    private $_items = array();

    /**
     * @var Settlement[] Массив оплат, обеспечивающих выдачу товара
     */
    private $_settlements = array();

    /**
     * @var ReceiptItem[] Список айтемов в заказе, являющихся доставкой
     */
    private $_shippingItems = array();

    /**
     * @var int Код системы налогообложения. Число 1-6.
     */
    private $_taxSystemCode;

    /**
     * Возвращает информацию о плательщике
     *
     * @return ReceiptCustomer  Информация о плательщике
     */
    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = new ReceiptCustomer();
        }
        return $this->_customer;
    }

    /**
     * Устанавливает информацию о плательщике
     * @param ReceiptCustomer $customer
     */
    public function setCustomer($customer)
    {
        $this->_customer = $customer;
    }

    /**
     * Возвращает список позиций в текущем чеке
     *
     * @return ReceiptItemInterface[] Список товаров в заказе
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Устанавливает список позиций в чеке
     *
     * Если до этого в чеке уже были установлены значения, они удаляются и полностью заменяются переданным списком
     * позиций. Все передаваемые значения в массиве позиций должны быть объектами класса, реализующего интерфейс
     * ReceiptItemInterface, в противном случае будет выброшено исключение InvalidPropertyValueTypeException.
     *
     * @param ReceiptItemInterface[] $value Список товаров в заказе
     *
     * @throws EmptyPropertyValueException Выбрасывается если передали пустой массив значений
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве значения был передан не массив и не
     * итератор, либо если одно из переданных значений не реализует интерфейс ReceiptItemInterface
     */
    public function setItems($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty items value in receipt', 0, 'receipt.items');
        }
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid items value type in receipt', 0, 'receipt.items', $value
            );
        }
        $this->_items         = array();
        $this->_shippingItems = array();
        foreach ($value as $key => $val) {
            if (is_object($val) && $val instanceof ReceiptItemInterface) {
                $this->addItem($val);
            } else {
                throw new InvalidPropertyValueTypeException(
                    'Invalid item value type in receipt', 0, 'receipt.items['.$key.']', $val
                );
            }
        }
    }

    /**
     * Добавляет товар в чек
     *
     * @param ReceiptItemInterface $value Объект добавляемой в чек позиции
     */
    public function addItem($value)
    {
        $this->_items[] = $value;
        if ($value->isShipping()) {
            $this->_shippingItems[] = $value;
        }
    }

    /**
     * Возвращает массив оплат, обеспечивающих выдачу товара
     *
     * @return SettlementInterface[] Массив оплат, обеспечивающих выдачу товара.
     */
    public function getSettlements()
    {
        return $this->_settlements;
    }

    /**
     * Возвращает массив оплат, обеспечивающих выдачу товара
     *
     * @param SettlementInterface[] $value
     */
    public function setSettlements($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty settlements value in receipt', 0, 'receipt.settlements');
        }
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid settlements value type in receipt', 0, 'receipt.settlements', $value
            );
        }
        $this->_settlements = array();
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                $this->addSettlement(new Settlement($val));
            } elseif ($val instanceof SettlementInterface) {
                $this->addSettlement($val);
            } else {
                throw new InvalidPropertyValueTypeException(
                    'Invalid settlements value type in receipt', 0, 'receipt.settlements['.$key.']', $val
                );
            }
        }
    }

    /**
     * Добавляет оплату в чек
     *
     * @param SettlementInterface $value Объект добавляемой в чек позиции
     */
    public function addSettlement($value)
    {
        $this->_settlements[] = $value;
    }

    /**
     * Возвращает код системы налогообложения
     *
     * @return int Код системы налогообложения. Число 1-6.
     */
    public function getTaxSystemCode()
    {
        return $this->_taxSystemCode;
    }

    /**
     * Устанавливает код системы налогообложения
     *
     * @param int $value Код системы налогообложения. Число 1-6
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент - не число
     * @throws InvalidPropertyValueException Выбрасывается если переданный аргумент меньше одного или больше шести
     */
    public function setTaxSystemCode($value)
    {
        if ($value === null || $value === '') {
            $this->_taxSystemCode = null;
        } elseif (!is_numeric($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid taxSystemCode value type', 0, 'receipt.taxSystemCode'
            );
        } else {
            $castedValue = (int)$value;
            if ($castedValue < 1 || $castedValue > 6) {
                throw new InvalidPropertyValueException(
                    'Invalid taxSystemCode value: '.$value, 0, 'receipt.taxSystemCode'
                );
            }
            $this->_taxSystemCode = $castedValue;
        }
    }

    /**
     * Проверяет есть ли в чеке хотя бы одна позиция
     *
     * @return bool True если чек не пуст, false если в чеке нет ни одной позиции
     */
    public function notEmpty()
    {
        return !empty($this->_items);
    }

    /**
     * Возвращает стоимость заказа исходя из состава чека
     *
     * @param bool $withShipping Добавить ли к стоимости заказа стоимость доставки
     *
     * @return int Общая стоимость заказа в центах/копейках
     */
    public function getAmountValue($withShipping = true)
    {
        $result = 0;
        foreach ($this->_items as $item) {
            if ($withShipping || !$item->isShipping()) {
                $result += $item->getAmount();
            }
        }

        return $result;
    }

    /**
     * Возвращает стоимость доставки исходя из состава чека
     *
     * @return int Стоимость доставки из состава чека в центах/копейках
     */
    public function getShippingAmountValue()
    {
        $result = 0;
        foreach ($this->_items as $item) {
            if ($item->isShipping()) {
                $result += $item->getAmount();
            }
        }

        return $result;
    }

    /**
     * Подгоняет стоимость товаров в чеке к общей цене заказа
     *
     * @param AmountInterface $orderAmount Общая стоимость заказа
     * @param bool $withShipping Поменять ли заодно и цену доставки
     */
    public function normalize(AmountInterface $orderAmount, $withShipping = false)
    {
        $amount = $orderAmount->getIntegerValue();
        if (!$withShipping) {
            if ($this->_shippingItems !== null) {
                if ($amount > $this->getShippingAmountValue()) {
                    $amount -= $this->getShippingAmountValue();
                } else {
                    $withShipping = true;
                }
            }
        }
        $realAmount = $this->getAmountValue($withShipping);

        if ($realAmount !== $amount) {
            $coefficient = (float)$amount / (float)$realAmount;
            $items       = array();
            $realAmount  = 0;
            foreach ($this->_items as $item) {
                if ($withShipping || !$item->isShipping()) {
                    $price = round($coefficient * $item->getPrice()->getIntegerValue());
                    if ($price < 1.0) {
                        if ($item->getPrice()->getIntegerValue() > 1) {
                            $item->getPrice()->setValue(0.01);
                        }
                        $amount -= $item->getAmount();
                    } else {
                        $items[]    = $item;
                        $realAmount += $item->getAmount();
                    }
                }
            }
            uasort($items, function (ReceiptItemInterface $a, ReceiptItemInterface $b) {
                if ($a->getPrice()->getIntegerValue() > $b->getPrice()->getIntegerValue()) {
                    return -1;
                }
                if ($a->getPrice()->getIntegerValue() < $b->getPrice()->getIntegerValue()) {
                    return 1;
                }

                return 0;
            });

            $coefficient = (float)$amount / (float)$realAmount;
            $realAmount  = 0;
            $aloneId     = null;
            foreach ($items as $index => $item) {
                if ($withShipping || !$item->isShipping()) {
                    $item->applyDiscountCoefficient($coefficient);
                    $realAmount += $item->getAmount();
                    if ($aloneId === null && $item->getQuantity() === 1.0 && !$item->isShipping()) {
                        $aloneId = $index;
                    }
                }
            }
            if ($aloneId === null) {
                foreach ($this->_items as $index => $item) {
                    if (!$item->isShipping()) {
                        $aloneId = $index;
                        break;
                    }
                }
            }
            if ($aloneId === null) {
                $aloneId = 0;
            }
            $diff = $amount - $realAmount;
            if (abs($diff) >= 0.1) {
                if ($this->_items[$aloneId]->getQuantity() === 1.0) {
                    $this->_items[$aloneId]->increasePrice($diff / 100.0);
                } elseif ($this->_items[$aloneId]->getQuantity() > 1.0) {
                    $item = $this->_items[$aloneId]->fetchItem(1);
                    $item->increasePrice($diff / 100.0);
                    array_splice($this->_items, $aloneId + 1, 0, array($item));
                } else {
                    $item = $this->_items[$aloneId]->fetchItem($this->_items[$aloneId]->getQuantity() / 2);
                    $item->increasePrice($diff / 100.0);
                    array_splice($this->_items, $aloneId + 1, 0, array($item));
                }
            }
        }
    }

    /**
     * Возвращает номер телефона плательщика в формате ITU-T E.164 на который будет выслан чек
     *
     * @deprecated 1.3.0 Устарел — данные рекомендуется брать в параметре receipt.customer.phone.
     *
     * @return string Номер телефона плательщика
     */
    public function getPhone()
    {
        return $this->getCustomer() ? $this->getCustomer()->getPhone() : null;
    }

    /**
     * Устанавливает номер телефона плательщика в формате ITU-T E.164 на который будет выслан чек
     *
     * @deprecated 1.3.0 Устарел — данные рекомендуется передавать в параметре receipt.customer.phone.
     *
     * @param string $value Номер телефона плательщика в формате ITU-T E.164
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве значения была передана не строка
     */
    public function setPhone($value)
    {
        if (!$this->getCustomer()) {
            $this->setCustomer(new ReceiptCustomer());
        }
        $this->getCustomer()->setPhone($value);
    }

    /**
     * Возвращает адрес электронной почты на который будет выслан чек
     *
     * @deprecated 1.3.0 Устарел — данные рекомендуется брать в параметре receipt.customer.email.
     *
     * @return string E-mail адрес плательщика
     */
    public function getEmail()
    {
        return $this->getCustomer() ? $this->getCustomer()->getEmail() : null;
    }

    /**
     * Устанавливает адрес электронной почты на который будет выслан чек
     *
     * @deprecated 1.3.0 Устарел — данные рекомендуется передавать в параметре receipt.customer.email.
     *
     * @param string $value E-mail адрес плательщика
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве значения была передана не строка
     */
    public function setEmail($value)
    {
        if (!$this->getCustomer()) {
            $this->setCustomer(new ReceiptCustomer());
        }
        $this->getCustomer()->setEmail($value);
    }

    /**
     * Устанавливает значения свойств текущего объекта из массива
     *
     * @param array|\Traversable $sourceArray Ассоциативный массив с настройками
     */
    public function fromArray($sourceArray)
    {
        if (!empty($sourceArray['customer'])) {
            $sourceArray['customer'] = new ReceiptCustomer($sourceArray['customer']);
        }

        if (!empty($sourceArray['items'])) {
            foreach ($sourceArray['items'] as $i => $itemArray) {
                if (is_array($itemArray)) {
                    $sourceArray['items'][$i] = new ReceiptItem($itemArray);
                }
            }
        }

        if (!empty($sourceArray['settlements'])) {
            foreach ($sourceArray['settlements'] as $i => $itemArray) {
                if (is_array($itemArray)) {
                    $sourceArray['settlements'][$i] = new Settlement($itemArray);
                }
            }
        }

        parent::fromArray($sourceArray);
    }

    /**
     * Возвращает Id объекта чека
     *
     * @return string Id объекта чека
     */
    public function getObjectId()
    {
        return null;
    }

}
