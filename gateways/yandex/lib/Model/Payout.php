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

use DateTime;
use YooKassa\Common\AbstractObject;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\Deal\PayoutDealInfo;
use YooKassa\Model\PaymentMethod\AbstractPaymentMethod;
use YooKassa\Model\Payout\AbstractPayoutDestination;
use YooKassa\Model\Payout\PayoutCancellationDetails;
use YooKassa\Model\Payout\PayoutDestinationFactory;

/**
 * Payout - Данные о выплате
 *
 * @property string $id Идентификатор выплаты
 * @property AmountInterface $amount Сумма выплаты
 * @property string $status Текущее состояние выплаты
 * @property AbstractPaymentMethod $payoutDestination Способ проведения выплаты
 * @property AbstractPaymentMethod $payout_destination Способ проведения выплаты
 * @property string $description Описание транзакции
 * @property DateTime $createdAt Время создания заказа
 * @property DateTime $created_at Время создания заказа
 * @property PayoutDealInfo $deal Сделка, в рамках которой нужно провести выплату
 * @property CancellationDetailsInterface $cancellationDetails Комментарий к отмене выплаты
 * @property CancellationDetailsInterface $cancellation_details Комментарий к отмене выплаты
 * @property Metadata $metadata Метаданные выплаты указанные мерчантом
 * @property bool $test Признак тестовой операции
 */
class Payout extends AbstractObject implements PayoutInterface
{
    /** Максимальная длина строки описания выплаты */
    const MAX_LENGTH_DESCRIPTION = 128;

    /**
     * @var string Идентификатор выплаты
     */
    private $_id;

    /**
     * @var AmountInterface Сумма выплаты
     */
    private $_amount;

    /**
     * @var string Текущее состояние выплаты
     */
    private $_status;

    /**
     * @var AbstractPaymentMethod Способ проведения выплаты
     */
    private $_payout_destination;

    /**
     * @var string Описание транзакции
     */
    private $_description;

    /**
     * @var DateTime Время создания выплаты
     */
    private $_createdAt;

    /**
     * @var PayoutDealInfo Сделка, в рамках которой нужно провести выплату. Присутствует, если вы проводите Безопасную сделку
     */
    private $_deal;

    /**
     * @var PayoutCancellationDetails Комментарий к статусу canceled: кто отменил выплаты и по какой причине
     */
    private $_cancellationDetails;

    /**
     * @var Metadata Метаданные выплаты указанные мерчантом
     */
    private $_metadata;

    /**
     * @var boolean Признак тестовой операции
     */
    private $_test;

    /**
     * Возвращает идентификатор выплаты
     * @return string Идентификатор выплаты
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанавливает идентификатор выплаты
     * @param string $value Идентификатор выплаты
     *
     * @throws InvalidPropertyValueException Выбрасывается если длина переданной строки не равна 36
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setId($value)
    {
        if (TypeCast::canCastToString($value)) {
            $length = mb_strlen($value, 'utf-8');
            if ($length < 36 || $length > 50) {
                throw new InvalidPropertyValueException('Invalid Payout id value', 0, 'Payout.id', $value);
            }
            $this->_id = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid Payout id value type', 0, 'Payout.id', $value);
        }
    }

    /**
     * Возвращает сумму
     * @return AmountInterface Сумма выплаты
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * Устанавливает сумму выплаты
     * @param AmountInterface|array $value Сумма выплаты
     */
    public function setAmount($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty amount value', 0, 'Payout.amount');
        } elseif ($value instanceof AmountInterface) {
            $this->_amount = $value;
        } elseif (is_array($value)) {
            $this->_amount = new MonetaryAmount($value);
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid Payout.amount value type', 0, 'Payout.amount', $value
            );
        }
    }

    /**
     * Возвращает состояние выплаты
     * @return string Текущее состояние выплаты
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Устанавливает статус выплаты
     * @param string $value Статус выплаты
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданная строка не является валидным статусом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setStatus($value)
    {
        if (TypeCast::canCastToEnumString($value)) {
            if (!PayoutStatus::valueExists((string)$value)) {
                throw new InvalidPropertyValueException('Invalid Payout status value', 0, 'Payout.status', $value);
            }
            $this->_status = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid Payout status value type', 0, 'Payout.status', $value
            );
        }
    }

    /**
     * Возвращает описание транзакции
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Устанавливает описание транзакции
     * @param string $value
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение превышает допустимую длину
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является строкой
     */
    public function setDescription($value)
    {
        if ($value === null || $value === '') {
            $this->_description = null;
        } elseif (TypeCast::canCastToString($value)) {
            $length = mb_strlen((string)$value, 'utf-8');
            if ($length > self::MAX_LENGTH_DESCRIPTION) {
                throw new InvalidPropertyValueException(
                    'The value of the description parameter is too long. Max length is ' . self::MAX_LENGTH_DESCRIPTION,
                    0,
                    'CreatePayoutRequest.description',
                    $value
                );
            }
            $this->_description = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid description value type', 0, 'CreatePayoutRequest.description', $value
            );
        }
    }

    /**
     * Возвращает используемый способ проведения выплаты
     * @return AbstractPaymentMethod Способ проведения выплаты
     */
    public function getPayoutDestination()
    {
        return $this->_payout_destination;
    }

    /**
     * Устанавливает используемый способ проведения выплаты
     * @param AbstractPayoutDestination|array $value Способ проведения выплаты
     */
    public function setPayoutDestination($value)
    {
        if ($value === null || $value === '') {
            $this->_payout_destination = null;
        } elseif ($value instanceof AbstractPayoutDestination) {
            $this->_payout_destination = $value;
        } elseif (is_array($value)) {
            $factory = new PayoutDestinationFactory();
            $this->_payout_destination  = $factory->factoryFromArray($value);
        }  else {
            throw new InvalidPropertyValueTypeException('Invalid payout_destination value type', 0, 'Payout.payout_destination', $value);
        }

        return $this;
    }

    /**
     * Возвращает время создания заказа
     * @return DateTime Время создания заказа
     */
    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    /**
     * Устанавливает время создания заказа
     * @param DateTime|string|int $value Время создания заказа
     *
     * @throws EmptyPropertyValueException Выбрасывается если в метод была передана пустая дата
     * @throws InvalidPropertyValueException Выбрасывается если передали строку, которую не удалось привести к дате
     * @throws InvalidPropertyValueTypeException|\Exception Выбрасывается если был передан аргумент, который невозможно
     * интерпретировать как дату или время
     */
    public function setCreatedAt($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty created_at value', 0, 'Payout.createdAt');
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException('Invalid created_at value', 0, 'Payout.createdAt', $value);
            }
            $this->_createdAt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid created_at value', 0, 'Payout.createdAt', $value);
        }
    }

    /**
     * Возвращает метаданные выплаты установленные мерчантом
     * @return Metadata Метаданные выплаты указанные мерчантом
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     * Устанавливает метаданные выплаты
     * @param Metadata|array $value Метаданные выплаты указанные мерчантом
     */
    public function setMetadata($value)
    {
        if ($value === null || $value === '') {
            $this->_metadata = null;
        } elseif (is_array($value)) {
            $this->_metadata = new Metadata($value);
        } elseif ($value instanceof Metadata) {
            $this->_metadata = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "metadata" parameter in Payout', 0, 'Payout.metadata', $value
            );
        }
        return $this;
    }

    /**
     * Возвращает комментарий к статусу canceled: кто отменил платеж и по какой причине
     * @return PayoutCancellationDetails|null Комментарий к статусу canceled
     * @since 1.0.13
     */
    public function getCancellationDetails()
    {
        return $this->_cancellationDetails;
    }

    /**
     * Устанавливает комментарий к статусу canceled: кто отменил платеж и по какой причине
     * @param PayoutCancellationDetails|array|null $value Комментарий к статусу canceled
     */
    public function setCancellationDetails($value)
    {
        if ($value === null) {
            $this->_cancellationDetails = null;
        } elseif (is_array($value)) {
            $this->_cancellationDetails = new PayoutCancellationDetails($value);
        } elseif ($value instanceof PayoutCancellationDetails) {
            $this->_cancellationDetails = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "cancellation_details" parameter in Payout', 0, 'Payout.cancellation_details', $value
            );
        }
    }

    /**
     * Возвращает признак тестовой операции
     * @return bool Признак тестовой операции
     */
    public function getTest()
    {
        return $this->_test;
    }

    /**
     * Устанавливает признак тестовой операции
     * @param bool $value Признак тестовой операции
     */
    public function setTest($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty Payout test flag value', 0, 'Payout.test');
        } elseif (TypeCast::canCastToBoolean($value)) {
            $this->_test = (bool)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid Payout test flag value type', 0, 'Payout.test', $value
            );
        }
    }

    /**
     * Возвращает сделку, в рамках которой нужно провести выплату
     * @return PayoutDealInfo Сделка, в рамках которой нужно провести выплату
     */
    public function getDeal()
    {
        return $this->_deal;
    }

    /**
     * Устанавливает сделку, в рамках которой нужно провести выплату
     * @param PayoutDealInfo|array $value Сделка, в рамках которой нужно провести выплату
     */
    public function setDeal($value)
    {
        if ($value === null || $value === '') {
            $this->_deal = null;
        } elseif (is_array($value)) {
            $this->_deal = new PayoutDealInfo($value);
        } elseif ($value instanceof PayoutDealInfo) {
            $this->_deal = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid value type for "deal" parameter in Payout', 0, 'Payout.deal', $value
            );
        }
        return $this;
    }
}
