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

namespace YooKassa\Model;

use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;

/**
 * Класс объекта с информацией о возврате платежа
 *
 * @property string $id Идентификатор возврата платежа
 * @property string $paymentId Идентификатор платежа
 * @property string $payment_id Идентификатор платежа
 * @property string $status Статус возврата
 * @property \DateTime $createdAt Время создания возврата
 * @property \DateTime $created_at Время создания возврата
 * @property AmountInterface $amount Сумма возврата
 * @property string $receiptRegistration Статус регистрации чека
 * @property string $receipt_registration Статус регистрации чека
 * @property string $description Комментарий, основание для возврата средств покупателю
 */
class Refund extends AbstractObject implements RefundInterface
{
    /**
     * @var string Идентификатор возврата платежа
     */
    private $_id;

    /**
     * @var string Идентификатор платежа
     */
    private $_paymentId;

    /**
     * @var string Статус возврата
     */
    private $_status;

    /**
     * @var \DateTime Время создания возврата
     */
    private $_createdAt;

    /**
     * @var MonetaryAmount Сумма возврата
     */
    private $_amount;

    /**
     * @var string Статус регистрации чека
     */
    private $_receiptRegistration;

    /**
     * @var string Комментарий, основание для возврата средств покупателю
     */
    private $_description;

    /**
     * @var SourceInterface[] Данные о распределении денег — сколько и в какой магазин нужно перевести.
     */
    private $_sources;

    /**
     * @var RequestorInterface
     */
    private $_requestor;


    /**
     * Возвращает идентификатор возврата платежа
     * @return string Идентификатор возврата
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанавливает идентификатор возврата
     * @param string $value Идентификатор возврата
     *
     * @throws EmptyPropertyValueException Выбрасывается если был передан пустой аргумент
     * @throws InvalidPropertyValueException Выбрасывается если було передано невалидное значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если аргумент не является строкой
     */
    public function setId($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty refund id', 0, 'Refund.id');
        } elseif (TypeCast::canCastToString($value)) {
            $castedValue = (string)$value;
            $length = mb_strlen($castedValue, 'utf-8');
            if ($length === 36) {
                $this->_id = $castedValue;
            } else {
                throw new InvalidPropertyValueException('Invalid refund id value', 0, 'Refund.id', $value);
            }
        } else {
            throw new InvalidPropertyValueTypeException('Invalid refund id value type', 0, 'Refund.id', $value);
        }
    }

    /**
     * Возвращает идентификатор платежа
     * @return string Идентификатор платежа
     */
    public function getPaymentId()
    {
        return $this->_paymentId;
    }

    /**
     * Устанавливает идентификатор платежа
     * @param string $value Идентификатор платежа
     *
     * @throws EmptyPropertyValueException Выбрасывается если был передан пустой аргумент
     * @throws InvalidPropertyValueException Выбрасывается если було передано невалидное значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если аргумент не является строкой
     */
    public function setPaymentId($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty refund paymentId', 0, 'Refund.paymentId');
        } elseif (TypeCast::canCastToString($value)) {
            $castedValue = (string)$value;
            $length = mb_strlen($castedValue, 'utf-8');
            if ($length === 36) {
                $this->_paymentId = $castedValue;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid refund paymentId value', 0, 'Refund.paymentId', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid refund paymentId value type', 0, 'Refund.paymentId', $value
            );
        }
    }

    /**
     * Возвращает статус текущего возврата
     * @return string Статус возврата
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Усианавливает стутус возврата платежа
     * @param string $value Статус возврата платежа
     *
     * @throws EmptyPropertyValueException Выбрасывается если был передан пустой аргумент
     * @throws InvalidPropertyValueException Выбрасывается если було передано невалидное значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если аргумент не является строкой
     */
    public function setStatus($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty refund status', 0, 'Refund.status');
        } elseif (TypeCast::canCastToEnumString($value)) {
            $castedValue = (string)$value;
            if (RefundStatus::valueExists($castedValue)) {
                $this->_status = $castedValue;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid refund status value', 0, 'Refund.status', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid refund status value type', 0, 'Refund.status', $value
            );
        }
    }

    /**
     * Возвращает дату создания возврата
     * @return \DateTime Время создания возврата
     */
    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    /**
     * Устанавливает вермя создания возврата
     * @param \DateTime $value Время создания возврата
     *
     * @throws EmptyPropertyValueException Выбрасывается если быо передано пустое значение
     * @throws InvalidPropertyValueException Выбрасывается если переданную строку или число не удалось интерпретировать
     * как дату и время
     * @throws InvalidPropertyValueTypeException|\Exception Выбрасывается если было передано значение невалидного типа
     */
    public function setCreatedAt($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty refund created_at value', 0, 'Refund.createdAt');
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException('Invalid created_at value', 0, 'Refund.createdAt', $value);
            }
            $this->_createdAt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid created_at value', 0, 'Refund.createdAt', $value);
        }
    }

    /**
     * Возвращает сумму возврата
     * @return AmountInterface Сумма возврата
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * Устанавливает сумму возврата
     * @param AmountInterface $value Сумма возврата
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданная сумма меньше или равна нулю
     */
    public function setAmount(AmountInterface $value)
    {
        if ($value->getIntegerValue() <= 0) {
            throw new InvalidPropertyValueException('Invalid refund amount', 0, 'Refund.amount', $value->getValue());
        }
        $this->_amount = $value;
    }

    /**
     * Возвращает статус регистрации чека
     * @return string Статус регистрации чека
     */
    public function getReceiptRegistration()
    {
        return $this->_receiptRegistration;
    }

    /**
     * Устанавливает статус регистрации чека
     * @param string $value Статус регистрации чека
     *
     * @throws EmptyPropertyValueException Выбрасывается если был передан пустой аргумент
     * @throws InvalidPropertyValueException Выбрасывается если було передано невалидное значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если аргумент не является строкой
     */
    public function setReceiptRegistration($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty refund receiptRegistration', 0, 'Refund.receiptRegistration');
        } elseif (TypeCast::canCastToEnumString($value)) {
            $castedValue = (string)$value;
            if (ReceiptRegistrationStatus::valueExists($castedValue)) {
                $this->_receiptRegistration = $castedValue;
            } else {
                throw new InvalidPropertyValueException(
                    'Invalid refund receiptRegistration value', 0, 'Refund.receiptRegistration', $value
                );
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid refund receiptRegistration value type', 0, 'Refund.receiptRegistration', $value
            );
        }
    }

    /**
     * Возвращает комментарий к возврату
     * @return string Комментарий, основание для возврата средств покупателю
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Устанавливает комментарий к возврату
     * @param string $value Комментарий, основание для возврата средств покупателю
     *
     * @throws EmptyPropertyValueException Выбрасывается если был передан пустой аргумент
     * @throws InvalidPropertyValueTypeException Выбрасывается если аргумент не является строкой
     */
    public function setDescription($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty refund description', 0, 'Refund.description');
        } elseif (TypeCast::canCastToEnumString($value)) {
            $this->_description = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException('Empty refund description', 0, 'Refund.description', $value);
        }
    }

    /**
     * @return SourceInterface[]
     */
    public function getSources()
    {
        return $this->_sources;
    }

    /**
     * Устанавливает sources (массив распределения денег между магазинами)
     * @param SourceInterface[]|array $value
     */
    public function setSources($value)
    {
        if (!is_array($value)) {
            $message = 'Sources must be an array of SourceInterface';
            throw new InvalidPropertyValueTypeException($message, 0, 'Refund.sources', $value);
        }

        $sources = array();
        foreach ($value as $item) {
            if (is_array($item)) {
                $item = new Source($item);
            }

            if (!($item instanceof SourceInterface)) {
                $message = 'Source must be instance of SourceInterface';
                throw new InvalidPropertyValueTypeException($message, 0, 'Refund.sources', $value);
            }
            $sources[] = $item;
        }

        $this->_sources = $sources;
    }

    /**
     * @return RequestorInterface
     */
    public function getRequestor()
    {
        return $this->_requestor;
    }

    /**
     * @param $value
     */
    public function setRequestor($value)
    {
        if (is_array($value)) {
            $value = new Requestor($value);
        }

        if (!($value instanceof RequestorInterface)) {
            throw new InvalidPropertyValueTypeException('Invalid Requestor type', 0, 'Refund.requestor', $value);
        }

        $this->_requestor = $value;
    }
}
