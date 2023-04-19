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
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\Deal\DealBalanceAmount;
use YooKassa\Model\Deal\DealStatus;
use YooKassa\Model\Deal\DealType;

/**
 * Class SafeBaseDeal
 *
 * @package YooKassa
 */
class SafeDeal extends BaseDeal implements DealInterface
{
    /**
     * Максимальная длина строки описания сделки
     */
    const MAX_LENGTH_DESCRIPTION = 128;

    /** @var string Идентификатор сделки */
    private $_id;

    /** @var string Статус сделки */
    private $_status;

    /** @var MonetaryAmount Баланс сделки */
    private $_balance;

    /** @var MonetaryAmount Сумма вознаграждения продавца */
    private $_payout_balance;

    /** @var string Описание сделки (не более 128 символов). Используется для фильтрации при получении списка сделок */
    private $_description;

    /** @var string Момент перечисления вам вознаграждения платформы */
    private $_fee_moment;

    /** @var DateTime Время создания сделки */
    private $_created_at;

    /** @var DateTime Время автоматического закрытия сделки */
    private $_expires_at;

    /** @var bool Признак тестовой операции */
    private $_test;

    /** @var Metadata Дополнительные данные, которые нужны вам для работы */
    private $_metadata;

    /**
     * SafeDeal constructor.
     * @param array $data
     */
    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->setType(DealType::SAFE_DEAL);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанавливает Id сделки
     *
     * @param string $value Id сделки
     * @return SafeDeal
     */
    public function setId($value)
    {
        $this->_id = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFeeMoment()
    {
        return $this->_fee_moment;
    }

    /**
     * Устанавливает момент перечисления вам вознаграждения платформы
     *
     * @param string $value Момент перечисления вам вознаграждения платформы
     * @return SafeDeal
     */
    public function setFeeMoment($value)
    {
        $this->_fee_moment = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBalance()
    {
        return $this->_balance;
    }

    /**
     * Устанавливает баланс сделки
     *
     * @param DealBalanceAmount|array $value Баланс сделки
     * @return SafeDeal
     */
    public function setBalance($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty balance value', 0, 'SafeDeal.balance');
        } elseif ($value instanceof AmountInterface) {
            $this->_balance = $value;
        } elseif (is_array($value)) {
            $this->_balance = new DealBalanceAmount();
            $this->_balance->fromArray($value);
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid SafeDeal.balance value type', 0, 'SafeDeal.balance', $value
            );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPayoutBalance()
    {
        return $this->_payout_balance;
    }

    /**
     * Устанавливает сумму вознаграждения продавца
     *
     * @param DealBalanceAmount $value Сумма вознаграждения продавца
     * @return SafeDeal
     */
    public function setPayoutBalance($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty payout_balance value', 0, 'SafeDeal.payout_balance');
        } elseif ($value instanceof AmountInterface) {
            $this->_payout_balance = $value;
        } elseif (is_array($value)) {
            $this->_payout_balance = new DealBalanceAmount();
            $this->_payout_balance->fromArray($value);
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid SafeDeal.payout_balance value type', 0, 'SafeDeal.payout_balance', $value
            );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Устанавливает статус сделки
     *
     * @param string $value Статус сделки
     * @return SafeDeal
     */
    public function setStatus($value)
    {
        if (TypeCast::canCastToEnumString($value)) {
            if (!DealStatus::valueExists((string)$value)) {
                throw new InvalidPropertyValueException('Invalid deal status value', 0, 'SafeDeal.status', $value);
            }
            $this->_status = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid deal status value type', 0, 'SafeDeal.status', $value
            );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->_created_at;
    }

    /**
     * Устанавливает время создания сделки
     *
     * @param DateTime|string $value Время создания сделки
     * @return SafeDeal
     *
     * @throws EmptyPropertyValueException Выбрасывается если в метод была передана пустая дата
     * @throws InvalidPropertyValueException Выбрасывается если передали строку, которую не удалось привести к дате
     * @throws InvalidPropertyValueTypeException|\Exception Выбрасывается если был передан аргумент, который невозможно
     * интерпретировать как дату или время
     */
    public function setCreatedAt($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty created_at value', 0, 'payment.createdAt');
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException('Invalid created_at value', 0, 'payment.createdAt', $value);
            }
            $this->_created_at = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid created_at value', 0, 'payment.createdAt', $value);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExpiresAt()
    {
        return $this->_expires_at;
    }

    /**
     * Устанавливает время автоматического закрытия сделки
     *
     * @param DateTime|string $value Время автоматического закрытия сделки
     * @return SafeDeal
     *
     * @throws EmptyPropertyValueException Выбрасывается если в метод была передана пустая дата
     * @throws InvalidPropertyValueException Выбрасывается если передали строку, которую не удалось привести к дате
     * @throws InvalidPropertyValueTypeException|\Exception Выбрасывается если был передан аргумент, который невозможно
     * интерпретировать как дату или время
     */
    public function setExpiresAt($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty expires_at value', 0, 'payment.expires_at');
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException('Invalid expires_at value', 0, 'payment.expires_at', $value);
            }
            $this->_expires_at = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid expires_at value', 0, 'payment.expires_at', $value);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTest()
    {
        return $this->_test;
    }

    /**
     * Устанавливает признак тестовой операции
     *
     * @param bool $value Признак тестовой операции
     * @return SafeDeal
     */
    public function setTest($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty SafeDeal test flag value', 0, 'SafeDeal.test');
        } elseif (TypeCast::canCastToBoolean($value)) {
            $this->_test = (bool)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid SafeDeal test flag value type', 0, 'SafeDeal.test', $value
            );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Устанавливает описание сделки (не более 128 символов).
     *
     * @param string $value Описание сделки
     * @return SafeDeal
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
                    'SafeDeal.description',
                    $value
                );
            }
            $this->_description = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid description value type', 0, 'SafeDeal.description', $value
            );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     * Устанавливает дополнительные данные сделки
     *
     * @param Metadata|array $value Дополнительные данные сделки
     * @return SafeDeal
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
                'Invalid value type for "metadata" parameter in SafeDeal', 0, 'SafeDeal.metadata', $value
            );
        }
        return $this;
    }
}
