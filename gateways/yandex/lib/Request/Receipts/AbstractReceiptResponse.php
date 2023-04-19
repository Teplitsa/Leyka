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

namespace YooKassa\Request\Receipts;

use DateTime;
use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\ReceiptRegistrationStatus;
use YooKassa\Model\ReceiptType;
use YooKassa\Model\Settlement;
use YooKassa\Model\SettlementInterface;

/**
 * Class AbstractReceipt
 *
 * @package YooKassa
 *
 * @property string $id Идентификатор чека в ЮKassa.
 * @property string $type Тип чека в онлайн-кассе: приход "payment" или возврат "refund".
 * @property string $status Статус доставки данных для чека в онлайн-кассу ("pending", "succeeded" или "canceled").
 * @property string $fiscalAttribute Фискальный признак чека. Формируется фискальным накопителем на основе данных, переданных для регистрации чека.
 * @property string $objectId Идентификатор объекта чека.
 * @property string $object_id Идентификатор объекта чека.
 * @property string $fiscal_attribute Фискальный признак чека. Формируется фискальным накопителем на основе данных, переданных для регистрации чека.
 * @property string $fiscalDocumentNumber Номер фискального документа.
 * @property string $fiscal_document_number Номер фискального документа.
 * @property string $fiscalStorageNumber Номер фискального накопителя в кассовом аппарате.
 * @property string $fiscal_storage_number Номер фискального накопителя в кассовом аппарате.
 * @property string $fiscalProviderId Идентификатор чека в онлайн-кассе. Присутствует, если чек удалось зарегистрировать.
 * @property string $fiscal_provider_id Идентификатор чека в онлайн-кассе. Присутствует, если чек удалось зарегистрировать.
 * @property \DateTime $registeredAt Дата и время формирования чека в фискальном накопителе.
 * @property \DateTime $registered_at Дата и время формирования чека в фискальном накопителе.
 * @property int $taxSystemCode Код системы налогообложения. Число 1-6.
 * @property int $tax_system_code Код системы налогообложения. Число 1-6.
 * @property ReceiptResponseItemInterface[] $items Список товаров в заказе
 * @property SettlementInterface[] $settlements Перечень совершенных расчетов.
 * @property string $onBehalfOf Идентификатор магазина
 * @property string $on_behalf_of Идентификатор магазина
 */
abstract class AbstractReceiptResponse extends AbstractObject implements ReceiptResponseInterface
{
    /** Длина идентификатора чека */
    const LENGTH_RECEIPT_ID = 39;

    /** @var string Идентификатор чека в ЮKassa. */
    private $_id;

    /** @var string Тип чека в онлайн-кассе: приход "payment" или возврат "refund". */
    private $_type;

    /** @var string Статус доставки данных для чека в онлайн-кассу "pending", "succeeded" или "canceled". */
    private $_status;

    /** @var string Номер фискального документа. */
    private $_fiscalDocumentNumber;

    /** @var string Номер фискального накопителя в кассовом аппарате. */
    private $_fiscalStorageNumber;

    /** @var string Идентификатор объекта чека */
    private $_object_id;

    /**
     * @var string Фискальный признак чека.
     * Формируется фискальным накопителем на основе данных, переданных для регистрации чека.
     */
    private $_fiscalAttribute;

    /**
     * @var \DateTime Дата и время формирования чека в фискальном накопителе.
     * Указывается в формате [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601).
     */
    private $_registeredAt;

    /** @var string Идентификатор чека в онлайн-кассе. Присутствует, если чек удалось зарегистрировать. */
    private $_fiscalProviderId;

    /** @var ReceiptResponseItemInterface[] Список товаров в заказе */
    private $_items = array();

    /** @var SettlementInterface[] Список оплат */
    private $_settlements = array();

    /** @var int Код системы налогообложения. Число 1-6. */
    private $_taxSystemCode;

    /** @var string Идентификатор магазина */
    private $_onBehalfOf;

    /**
     * AbstractReceiptResponse constructor.
     *
     * @param mixed $receiptData
     * @throws \Exception
     */
    public function fromArray($receiptData)
    {
        if (!empty($receiptData['id'])) {
            $this->setId($receiptData['id']);
        }
        if (!empty($receiptData['type'])) {
            $this->setType($receiptData['type']);
        }
        if (!empty($receiptData['refund_id']) || !empty($receiptData['payment_id'])) {
            $this->setObjectId($this->factoryObjectId($receiptData));
        }
        if (!empty($receiptData['status'])) {
            $this->setStatus($receiptData['status']);
        }

        if (!empty($receiptData['tax_system_code'])) {
            $this->setTaxSystemCode($receiptData['tax_system_code']);
        }
        if (!empty($receiptData['fiscal_document_number'])) {
            $this->setFiscalDocumentNumber($receiptData['fiscal_document_number']);
        }
        if (!empty($receiptData['fiscal_storage_number'])) {
            $this->setFiscalStorageNumber($receiptData['fiscal_storage_number']);
        }
        if (!empty($receiptData['fiscal_attribute'])) {
            $this->setFiscalAttribute($receiptData['fiscal_attribute']);
        }
        if (!empty($receiptData['registered_at'])) {
            $this->setRegisteredAt(new DateTime($receiptData['registered_at']));
        }
        if (!empty($receiptData['fiscal_provider_id'])) {
            $this->setFiscalProviderId($receiptData['fiscal_provider_id']);
        }
        if (!empty($receiptData['items'])) {
            if (is_array($receiptData['items']) && count($receiptData['items'])) {
                $itemsArray = array();
                foreach ($receiptData['items'] as $item) {
                    $itemsArray[] = new ReceiptResponseItem($item);
                }
                $this->setItems($itemsArray);
            } else {
                throw new EmptyPropertyValueException('Empty items value in receipt', 0, 'receipt.items');
            }
        } else {
            throw new EmptyPropertyValueException('Empty items value in receipt', 0, 'receipt.items');
        }
        if (!empty($receiptData['settlements'])) {
            if (is_array($receiptData['settlements']) && count($receiptData['settlements'])) {
                $itemsArray = array();
                foreach ($receiptData['settlements'] as $item) {
                    $itemsArray[] = new Settlement($item);
                }
                $this->setSettlements($itemsArray);
            } else {
                throw new EmptyPropertyValueException('Empty settlements value in receipt', 0, 'receipt.settlements');
            }
        }
        if (!empty($receiptData['on_behalf_of'])) {
            $this->setOnBehalfOf($receiptData['on_behalf_of']);
        }

        $this->setSpecificProperties($receiptData);
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанавливает идентификатор чека
     * @param string $value Идентификатор чека
     *
     * @throws InvalidPropertyValueException Выбрасывается если длина переданной строки не равна 40
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setId($value)
    {
        if (TypeCast::canCastToString($value)) {
            if (strlen((string)$value) !== self::LENGTH_RECEIPT_ID) {
                throw new InvalidPropertyValueException('Invalid receipt id value', 0, 'Receipt.id', $value);
            }
            $this->_id = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid receipt id value type', 0, 'Receipt.id', $value);
        }
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Устанавливает типа чека
     * @param string $value Тип чека
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданная строка не является валидным типом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
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
     * Возвращает идентификатор платежа или возврата, для которого был сформирован чек.
     * @return string
     */
    public function getObjectId()
    {
        return $this->_object_id;
    }

    /**
     * Устанавливает идентификатор платежа или возврата, для которого был сформирован чек
     *
     * @param $value
     */
    public function setObjectId($value)
    {
        if ($value === null || $value === '') {
            $this->_object_id = null;
        } elseif (TypeCast::canCastToString($value)) {
            $this->_object_id = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid receipt object_id type', 0, 'Receipt.object_id', $value);
        }
    }

    /**
     * Фабричный метод создания идентификатора объекта, для которого был сформирован чек
     *
     * @param array $receiptData Массив данных чека
     * @return string|null
     */
    private function factoryObjectId($receiptData)
    {
        if (array_key_exists('refund_id', $receiptData)) {
            return $receiptData['refund_id'];
        } elseif (array_key_exists('payment_id', $receiptData)) {
            return $receiptData['payment_id'];
        }
        return null;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Устанавливает состояние регистрации фискального чека
     * @param string $value Состояние регистрации фискального чека
     *
     * @return AbstractReceiptResponse
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное состояние регистрации не существует
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент не строка
     */
    public function setStatus($value)
    {
        if ($value === null || $value === '') {
            $this->_status = null;
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (ReceiptRegistrationStatus::valueExists($value)) {
                $this->_status = (string)$value;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid status value', 0, 'Receipt.status', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid status value type', 0, 'Receipt.status', $value
            );
        }
        return $this;
    }

    /**
     * Возвращает номер фискального документа
     * @return string Номер фискального документа
     */
    public function getFiscalDocumentNumber()
    {
        return $this->_fiscalDocumentNumber;
    }

    /**
     * Устанавливает номер фискального документа
     * @param string $value Номер фискального документа
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент не строка
     */
    public function setFiscalDocumentNumber($value)
    {
        if ($value === null || $value === '') {
            $this->_fiscalDocumentNumber = null;
        } elseif (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid fiscal_document_number value type', 0, 'Receipt.fiscalDocumentNumber', $value
            );
        } else {
            $this->_fiscalDocumentNumber = (string)$value;
        }
    }

    /**
     * Возвращает номер фискального накопителя в кассовом аппарате
     * @return string Номер фискального накопителя в кассовом аппарате
     */
    public function getFiscalStorageNumber()
    {
        return $this->_fiscalStorageNumber;
    }

    /**
     * Устанавливает номер фискального накопителя в кассовом аппарате
     * @param string $fiscal_storage_number Номер фискального накопителя в кассовом аппарате
     */
    public function setFiscalStorageNumber($fiscal_storage_number)
    {
        $this->_fiscalStorageNumber = $fiscal_storage_number;
    }

    /**
     * Возвращает фискальный признак чека
     * @return string Фискальный признак чека
     */
    public function getFiscalAttribute()
    {
        return $this->_fiscalAttribute;
    }

    /**
     * Устанавливает фискальный признак чека
     * @param string $fiscal_attribute Фискальный признак чека
     */
    public function setFiscalAttribute($fiscal_attribute)
    {
        $this->_fiscalAttribute = $fiscal_attribute;
    }

    /**
     * Возвращает дату и время формирования чека в фискальном накопителе
     * @return DateTime Дата и время формирования чека в фискальном накопителе
     */
    public function getRegisteredAt()
    {
        return $this->_registeredAt;
    }

    /**
     * Устанавливает дату и время формирования чека в фискальном накопителе
     * @param DateTime $registered_at Дата и время формирования чека в фискальном накопителе
     */
    public function setRegisteredAt($registered_at)
    {
        $this->_registeredAt = $registered_at;
    }

    /**
     * Возвращает идентификатор чека в онлайн-кассе
     * @return string Идентификатор чека в онлайн-кассе
     */
    public function getFiscalProviderId()
    {
        return $this->_fiscalProviderId;
    }

    /**
     * Устанавливает идентификатор чека в онлайн-кассе
     * @param string $fiscal_provider_id Идентификатор чека в онлайн-кассе
     */
    public function setFiscalProviderId($fiscal_provider_id)
    {
        $this->_fiscalProviderId = $fiscal_provider_id;
    }

    /**
     * @inheritdoc
     *
     * @return ReceiptResponseItem[]|ReceiptResponseItemInterface[]
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
     * @param ReceiptResponseItemInterface[] $value Список товаров в заказе
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
        $this->_items = array();
        foreach ($value as $key => $val) {
            if (is_object($val) && $val instanceof ReceiptResponseItemInterface) {
                $this->addItem($val);
            } else {
                throw new InvalidPropertyValueTypeException(
                    'Invalid item value type in receipt', 0, 'receipt.items[' . $key . ']', $val
                );
            }
        }
    }

    /**
     * Добавляет товар в чек
     *
     * @param ReceiptResponseItemInterface $value Объект добавляемой в чек позиции
     */
    public function addItem($value)
    {
        $this->_items[] = $value;
    }

    /**
     * Возвращает Массив оплат, обеспечивающих выдачу товара
     *
     * @return SettlementInterface[]
     */
    public function getSettlements()
    {
        return $this->_settlements;
    }

    /**
     * Устанавливает массив оплат, обеспечивающих выдачу товара
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
                    'Invalid settlement value type in receipt', 0, 'receipt.settlements['.$key.']', $val
                );
            }
        }
    }

    /**
     * Добавляет оплату в массив
     *
     * @param SettlementInterface $value
     */
    public function addSettlement(SettlementInterface $value)
    {
        $this->_settlements[] = $value;
    }


    /**
     * @inheritdoc
     *
     * @return int
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
                'Invalid tax_system_code value type', 0, 'receipt.taxSystemCode'
            );
        } else {
            $castedValue = (int)$value;
            if ($castedValue < 1 || $castedValue > 6) {
                throw new InvalidPropertyValueException(
                    'Invalid tax_system_code value: ' . $value, 0, 'receipt.taxSystemCode'
                );
            }
            $this->_taxSystemCode = $castedValue;
        }
    }

    /**
     * @inheritdoc
     *
     * @return string|null
     */
    public function getOnBehalfOf()
    {
        return $this->_onBehalfOf;
    }

    /**
     * Возвращает идентификатор магазина, от имени которого нужно отправить чек
     * @param string $value Идентификатор магазина, от имени которого нужно отправить чек
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
     * Проверяет есть ли в чеке хотя бы одна позиция
     *
     * @return bool True если чек не пуст, false если в чеке нет ни одной позиции
     */
    public function notEmpty()
    {
        return !empty($this->_items);
    }

    /**
     * Установка свойств, присущих конкретному объекту
     *
     * @param array $receiptData
     *
     * @return void
     */
    abstract public function setSpecificProperties($receiptData);
}
