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


use YooKassa\Common\AbstractRequest;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\ReceiptCustomer;
use YooKassa\Model\ReceiptCustomerInterface;
use YooKassa\Model\ReceiptItem;
use YooKassa\Model\ReceiptItemInterface;
use YooKassa\Model\ReceiptType;
use YooKassa\Model\Settlement;
use YooKassa\Model\SettlementInterface;

/**
 * Class AbstractPostReceiptRequest
 * @package YooKassa\Request\Receipts
 */
class CreatePostReceiptRequest extends AbstractRequest implements CreatePostReceiptRequestInterface
{
    /** @var ReceiptCustomerInterface Информация о плательщике */
    private $_customer;

    /** @var string Тип чека в онлайн-кассе: приход "payment" или возврат "refund". */
    private $_type;

    /** @var bool Признак отложенной отправки чека. */
    private $_send = true;

    /** @var int Код системы налогообложения. Число 1-6. */
    private $_taxSystemCode;

    /** @var ReceiptItemInterface[] Список товаров в заказе */
    private $_items = array();

    /** @var SettlementInterface[] Список платежей */
    private $_settlements = array();

    /** @var string Идентификатор объекта оплаты */
    private $_object_id;

    /** @var string Идентификатор магазина в ЮKassa */
    private $_onBehalfOf;

    /**
     * Возвращает билдер объектов запросов создания платежа
     * @return CreatePostReceiptRequestBuilder Инстанс билдера объектов запрсов
     */
    public static function builder()
    {
        return new CreatePostReceiptRequestBuilder();
    }

    /**
     * Возвращает Id объекта чека
     *
     * @return string Id объекта чека
     */
    public function getObjectId()
    {
        return $this->_object_id;
    }

    /**
     * Устанавливает Id объекта чека
     *
     * @param string $value
     */
    public function setObjectId($value)
    {
        $this->_object_id = $value;
    }

    /**
     * @return bool
     */
    public function hasCustomer()
    {
        return !empty($this->_customer);
    }

    /**
     * Возвращает информацию о плательщике
     *
     * @return ReceiptCustomerInterface информация о плательщике
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * Устанавливает информацию о плательщике
     *
     * @param ReceiptCustomerInterface $value
     */
    public function setCustomer($value)
    {
        if (is_array($value)) {
            $this->_customer = new ReceiptCustomer($value);
        } elseif (is_object($value) && $value instanceof ReceiptCustomerInterface) {
            $this->_customer = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid customer value type in receipt', 0, 'Receipt.customer', $value
            );
        }
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
            throw new EmptyPropertyValueException('Empty items value in receipt', 0, 'Receipt.items');
        }
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid items value type in receipt', 0, 'Receipt.items', $value
            );
        }
        $this->_items = array();
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                $this->addItem(new ReceiptItem($val));
            } elseif (is_object($val) && $val instanceof ReceiptItemInterface) {
                $this->addItem($val);
            } else {
                throw new InvalidPropertyValueTypeException(
                    'Invalid item value type in receipt', 0, 'Receipt.items['.$key.']', $val
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
            throw new EmptyPropertyValueException('Empty taxSystemCode value in receipt', 0, 'Receipt.taxSystemCode');
        } elseif (!is_numeric($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid taxSystemCode value type', 0, 'Receipt.taxSystemCode'
            );
        } else {
            $castedValue = (int)$value;
            if ($castedValue < 1 || $castedValue > 6) {
                throw new InvalidPropertyValueException(
                    'Invalid taxSystemCode value: '.$value, 0, 'Receipt.taxSystemCode'
                );
            }
            $this->_taxSystemCode = $castedValue;
        }
    }

    /**
     * Возвращает тип чека в онлайн-кассе
     *
     * @return string Тип чека в онлайн-кассе: приход "payment" или возврат "refund".
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Устанавливает тип чека в онлайн-кассе
     *
     * @param string $value Тип чека в онлайн-кассе: приход "payment" или возврат "refund".
     */
    public function setType($value)
    {
        if (TypeCast::canCastToEnumString($value)) {
            if (!ReceiptType::valueExists((string)$value)) {
                throw new InvalidPropertyValueException('Invalid receipt type value', 0, 'Receipt.type', $value);
            }
            $this->_type = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid receipt type value type', 0, 'Receipt.type', $value
            );
        }
    }

    /**
     * Возвращает признак отложенной отправки чека.
     *
     * @return bool Признак отложенной отправки чека.
     */
    public function getSend()
    {
        return $this->_send;
    }

    /**
     * Устанавливает признак отложенной отправки чека.
     * @param bool $value Признак отложенной отправки чека.
     */
    public function setSend($value)
    {
        if (TypeCast::canCastToBoolean($value)) {
            $this->_send = (bool)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid receipt type value send', 0, 'Receipt.send', $value
            );
        }
    }

    /**
     * Возвращает массив оплат, обеспечивающих выдачу товара.
     *
     * @return SettlementInterface[] Массив оплат, обеспечивающих выдачу товара.
     */
    public function getSettlements()
    {
        return $this->_settlements;
    }

    /**
     * Устанавливает массив оплат, обеспечивающих выдачу товара.
     *
     * @param SettlementInterface[] $value Массив оплат, обеспечивающих выдачу товара.
     */
    public function setSettlements($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty settlements value in receipt', 0, 'Receipt.settlements');
        }
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid settlements value type in receipt', 0, 'Receipt.settlements', $value
            );
        }
        $this->_settlements = array();
        foreach ($value as $key => $val) {
            if (is_array($val) && !empty($val['type']) && !empty($val['amount'])) {
                $this->addSettlement(new Settlement($val));
            } elseif (is_object($val) && $val instanceof SettlementInterface) {
                $this->addSettlement($val);
            } else {
                throw new InvalidPropertyValueTypeException(
                    'Invalid settlement value type in receipt', 0, 'Receipt.settlements['.$key.']', $val
                );
            }
        }
    }

    /**
     * Добавляет оплату в перечень совершенных расчетов.
     *
     * @param SettlementInterface $value Информация о совершенных расчетах.
     */
    public function addSettlement(SettlementInterface $value)
    {
        $this->_settlements[] = $value;
    }

    /**
     * @return string
     */
    public function getOnBehalfOf()
    {
        return $this->_onBehalfOf;
    }

    /**
     * @param string $value
     */
    public function setOnBehalfOf($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty onBehalfOf value', 0, 'Receipt.onBehalfOf'
            );
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid onBehalfOf value type', 0, 'Receipt.onBehalfOf', $value
            );
        } else {
            $this->_onBehalfOf = (string)$value;
        }
    }

    /**
     * Проверяет есть ли в чеке хотя бы одна позиция товаров и оплат
     *
     * @return bool True если чек не пуст, false если в чеке нет ни одной позиции
     */
    public function notEmpty()
    {
        return !empty($this->_items) && !empty($this->_settlements);
    }

    /**
     * Устанавливает значения свойств текущего объекта из массива
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
     * Валидирует текущий запрос, проверяет все ли нужные свойства установлены
     * @return bool True если запрос валиден, false если нет
     */
    public function validate()
    {
        if (empty($this->_customer)) {
            $this->setValidationError('Receipt customer not specified');
            return false;
        }

        if (empty($this->_type) || !ReceiptType::valueExists($this->_type)) {
            $this->setValidationError('Receipt type not specified');
            return false;
        }

        if (empty($this->_send)) { // todo: пока может быть только true
            $this->setValidationError('Receipt send not specified');
            return false;
        }

        if (empty($this->_settlements)) {
            $this->setValidationError('Receipt settlements not specified');
            return false;
        }

        if (empty($this->_items)) {
            $this->setValidationError('Receipt items not specified');
            return false;
        }

        return true;
    }

}