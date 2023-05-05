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

namespace YooKassa\Request\Deals;

use YooKassa\Common\AbstractPaymentRequest;
use YooKassa\Common\AbstractRequest;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\AirlineInterface;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\Deal\DealType;
use YooKassa\Model\Deal\FeeMoment;
use YooKassa\Model\Payment;
use YooKassa\Model\PaymentData\AbstractPaymentData;
use YooKassa\Model\ConfirmationAttributes\AbstractConfirmationAttributes;
use YooKassa\Model\Metadata;
use YooKassa\Model\ReceiptInterface;
use YooKassa\Model\RecipientInterface;
use YooKassa\Model\SafeDeal;

/**
 * Класс объекта запроса к API на проведение новой сделки
 *
 * @todo: @example 02-builder.php 11 78 Пример использования билдера
 *
 * @package YooKassa
 *
 * @property string $type Тип сделки
 * @property string $feeMoment Момент перечисления вам вознаграждения платформы
 * @property string $fee_moment Момент перечисления вам вознаграждения платформы
 * @property string $description Описание сделки
 * @property Metadata $metadata Метаданные привязанные к сделке
 */
class CreateDealRequest extends AbstractRequest implements CreateDealRequestInterface
{
    /** @var string Тип сделки */
    private $_type;

    /** @var string Момент перечисления вам вознаграждения платформы */
    private $_fee_moment;

    /** @var string Описание сделки */
    private $_description;

    /** @var Metadata Метаданные привязанные к сделке */
    private $_metadata;

    /**
     * Возвращает тип сделки
     * @return string Тип сделки
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Устанавливает тип сделки
     * @param string $value Тип сделки
     *
     * @throws InvalidPropertyValueTypeException Генерируется если переданный аргумент не является строкой
     * @throws InvalidPropertyValueException Генерируется если переданный аргумент не из списка DealType
     */
    public function setType($value)
    {
        if (TypeCast::canCastToEnumString($value)) {
            if (!DealType::valueExists((string)$value)) {
                throw new InvalidPropertyValueException('Invalid deal type value', 0, 'CreateDealRequest.type', $value);
            }
            $this->_type = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid deal type value type', 0, 'CreateDealRequest.type', $value
            );
        }
    }

    /**
     * Проверяет наличие типа в создаваемой сделке
     * @return bool True если тип сделки есть, false если нет
     */
    public function hasType()
    {
        return $this->_type !== null;
    }

    /**
     * Возвращает момент перечисления вам вознаграждения платформы
     * @return string Момент перечисления вам вознаграждения платформы
     */
    public function getFeeMoment()
    {
        return $this->_fee_moment;
    }

    /**
     * Проверяет, был ли установлен момент перечисления вознаграждения
     * @return bool True если момент перечисления был установлен, false если нет
     */
    public function hasFeeMoment()
    {
        return $this->_fee_moment !== null;
    }

    /**
     * Устанавливает момент перечисления вам вознаграждения платформы
     * @param string $value Момент перечисления вам вознаграждения платформы
     *
     * @throws InvalidPropertyValueTypeException Генерируется если переданный аргумент не является строкой
     * @throws InvalidPropertyValueException Генерируется если переданный аргумент не из списка FeeMoment
     */
    public function setFeeMoment($value)
    {
        if (TypeCast::canCastToEnumString($value)) {
            if (!FeeMoment::valueExists((string)$value)) {
                throw new InvalidPropertyValueException('Invalid deal fee_moment value', 0, 'CreateDealRequest.fee_moment', $value);
            }
            $this->_fee_moment = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid fee_moment value type in CreateDealRequest', 0, 'CreateDealRequest.fee_moment', $value
            );
        }
    }

    /**
     * Возвращает описание сделки
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Устанавливает описание сделки
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
            if ($length > SafeDeal::MAX_LENGTH_DESCRIPTION) {
                throw new InvalidPropertyValueException(
                    'The value of the description parameter is too long. Max length is ' . SafeDeal::MAX_LENGTH_DESCRIPTION,
                    0,
                    'CreateDealRequest.description',
                    $value
                );
            }
            $this->_description = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid description value type', 0, 'CreateDealRequest.description', $value
            );
        }
    }

    /**
     * Проверяет наличие описания в создаваемой сделке
     * @return bool True если описание сделки есть, false если нет
     */
    public function hasDescription()
    {
        return $this->_description !== null;
    }

    /**
     * Возвращает данные оплаты установленные мерчантом
     * @return Metadata Метаданные, привязанные к платежу
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     * Проверяет, были ли установлены метаданные заказа
     * @return bool True если метаданные были установлены, false если нет
     */
    public function hasMetadata()
    {
        return !empty($this->_metadata) && $this->_metadata->count() > 0;
    }

    /**
     * Устанавливает метаданные, привязанные к платежу
     * @param Metadata|array|null $value Метаданные платежа, устанавливаемые мерчантом
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданные данные не удалось интерпретировать как
     * метаданные платежа
     */
    public function setMetadata($value)
    {
        if ($value === null || (is_array($value) && empty($value))) {
            $this->_metadata = null;
        } elseif ($value instanceof Metadata) {
            $this->_metadata = $value;
        } elseif (is_array($value)) {
            $this->_metadata = new Metadata($value);
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid metadata value type in CreateDealRequest', 0, 'CreateDealRequest.metadata', $value
            );
        }
    }

    /**
     * Проверяет на валидность текущий объект
     * @return bool True если объект запроса валиден, false если нет
     */
    public function validate()
    {
        if (!$this->hasType()) {
            $this->setValidationError('Type field is required');
            return false;
        }
        if (!$this->hasFeeMoment()) {
            $this->setValidationError('FeeMoment field is required');
            return false;
        }
        return true;
    }

    /**
     * Возвращает билдер объектов запросов создания сделки
     * @return CreateDealRequestBuilder Инстанс билдера объектов запросов
     */
    public static function builder()
    {
        return new CreateDealRequestBuilder();
    }
}
