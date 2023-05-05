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

namespace YooKassa\Request\Payouts;

use YooKassa\Common\AbstractRequest;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\Deal\PayoutDealInfo;
use YooKassa\Model\MonetaryAmount;
use YooKassa\Model\Payout;
use YooKassa\Model\Metadata;
use YooKassa\Model\Payout\AbstractPayoutDestination;
use YooKassa\Request\Payouts\PayoutDestinationData\AbstractPayoutDestinationData;
use YooKassa\Request\Payouts\PayoutDestinationData\PayoutDestinationDataFactory;

/**
 * Класс объекта запроса к API на проведение новой выплаты
 *
 * @todo: @example 02-builder.php 11 78 Пример использования билдера
 *
 * @package YooKassa
 *
 * @property AmountInterface $amount Сумма создаваемой выплаты
 * @property AbstractPayoutDestination $payoutDestinationData Данные платежного средства, на которое нужно сделать выплату. Обязательный параметр, если не передан payout_token.
 * @property AbstractPayoutDestination $payout_destination_data Данные платежного средства, на которое нужно сделать выплату. Обязательный параметр, если не передан payout_token.
 * @property string $payoutToken Токенизированные данные для выплаты. Например, синоним банковской карты. Обязательный параметр, если не передан payout_destination_data
 * @property string $payout_token Токенизированные данные для выплаты. Например, синоним банковской карты. Обязательный параметр, если не передан payout_destination_data
 * @property PayoutDealInfo $deal Сделка, в рамках которой нужно провести выплату. Необходимо передавать, если вы проводите Безопасную сделку
 * @property string $description Описание транзакции (не более 128 символов). Например: «Выплата по договору N»
 * @property Metadata $metadata Метаданные привязанные к выплате
 */
class CreatePayoutRequest extends AbstractRequest implements CreatePayoutRequestInterface
{
    /**
     * @var AmountInterface Сумма создаваемой выплаты
     */
    private $_amount;

    /**
     * @var AbstractPayoutDestination Данные платежного средства, на которое нужно сделать выплату
     */
    private $_payoutDestinationData;

    /**
     * @var string Токенизированные данные для выплаты
     */
    private $_payoutToken;

    /**
     * @var PayoutDealInfo Сделка, в рамках которой нужно провести выплату
     */
    private $_deal;

    /**
     * @var string Описание транзакции
     */
    private $_description;

    /**
     * @var Metadata Метаданные привязанные к выплате
     */
    private $_metadata;

    /**
     * Возвращает сумму выплаты
     * @return AmountInterface Сумма выплаты
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * Проверяет, была ли установлена сумма выплаты
     * @return bool True если сумма выплаты была установлена, false если нет
     */
    public function hasAmount()
    {
        return !empty($this->_amount);
    }

    /**
     * Устанавливает сумму выплаты
     * @param AmountInterface|array|string|null Сумма выплаты
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если был передан объект невалидного типа
     */
    public function setAmount($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty amount value', 0, 'CreatePayoutRequest.amount');
        } elseif ($value instanceof AmountInterface) {
            $this->_amount = $value;
        } elseif (is_numeric($value) || is_array($value)) {
            $this->_amount = new MonetaryAmount($value);
        } else {
            throw new InvalidPropertyValueTypeException('Invalid amount value type in CreatePayoutRequest', 0, 'CreatePayoutRequest.amount', $value);
        }
    }

    /**
     * Возвращает данные для создания метода оплаты
     * @return AbstractPayoutDestination Данные используемые для создания метода оплаты
     */
    public function getPayoutDestinationData()
    {
        return $this->_payoutDestinationData;
    }

    /**
     * Проверяет установлен ли объект с методом оплаты
     * @return bool True если объект метода оплаты установлен, false если нет
     */
    public function hasPayoutDestinationData()
    {
        return !empty($this->_payoutDestinationData);
    }

    /**
     * Устанавливает объект с информацией для создания метода оплаты
     * @param AbstractPayoutDestinationData|array|null $value Объект создания метода оплаты или null
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если был передан объект невалидного типа
     */
    public function setPayoutDestinationData($value)
    {
        if ($value === null || $value === '') {
            $this->_payoutDestinationData = null;
        } elseif ($value instanceof AbstractPayoutDestinationData) {
            $this->_payoutDestinationData = $value;
        } elseif (is_array($value)) {
            $factory = new PayoutDestinationDataFactory();
            $this->_payoutDestinationData = $factory->factoryFromArray($value);
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payoutDestinationData value type in CreatePayoutRequest',
                0,
                'CreatePayoutRequest.payoutDestinationData',
                $value
            );
        }
    }

    /**
     * Проверяет наличие токенизированных данных для выплаты
     * @return bool True если токен установлен, false если нет
     */
    public function getPayoutToken()
    {
        return $this->_payoutToken;
    }

    /**
     * Проверяет наличие одноразового токена для проведения оплаты
     * @return bool True если токен установлен, false если нет
     */
    public function hasPayoutToken()
    {
        return !empty($this->_payoutToken);
    }

    /**
     * Устанавливает токенизированные данные для выплаты
     * @param string $value Токенизированные данные для выплаты
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является строкой
     */
    public function setPayoutToken($value)
    {
        if ($value === null || $value === '') {
            $this->_payoutToken = null;
        } elseif (TypeCast::canCastToString($value)) {
            $this->_payoutToken = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payoutToken value type', 0, 'CreatePayoutRequest.payoutToken', $value
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
     * Проверяет наличие сделки в создаваемой выплате
     * @return bool True если сделка есть, false если нет
     */
    public function hasDeal()
    {
        return !empty($this->_deal);
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
                'Invalid value type for "deal" parameter in CreatePayoutRequest', 0, 'CreatePayoutRequest.deal', $value
            );
        }
        return $this;
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
     * Проверяет наличие описания транзакции в создаваемом платеже
     * @return bool True если описание транзакции есть, false если нет
     */
    public function hasDescription()
    {
        return $this->_description !== null;
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
            if ($length > Payout::MAX_LENGTH_DESCRIPTION) {
                throw new InvalidPropertyValueException(
                    'The value of the description parameter is too long. Max length is ' . Payout::MAX_LENGTH_DESCRIPTION,
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
     * Возвращает данные оплаты установленные мерчантом
     * @return Metadata Метаданные, привязанные к выплате
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     * Проверяет, были ли установлены метаданные выплаты
     * @return bool True если метаданные были установлены, false если нет
     */
    public function hasMetadata()
    {
        return !empty($this->_metadata) && $this->_metadata->count() > 0;
    }

    /**
     * Устанавливает метаданные, привязанные к выплате
     * @param Metadata|array|null $value Метаданные выплаты, устанавливаемые мерчантом
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданные данные не удалось интерпретировать как
     * метаданные выплаты
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
                'Invalid metadata value type in CreatePayoutRequest', 0, 'CreatePayoutRequest.metadata', $value
            );
        }
    }

    /**
     * Проверяет на валидность текущий объект
     * @return bool True если объект запроса валиден, false если нет
     */
    public function validate()
    {
        if (!$this->hasAmount()) {
            $this->setValidationError('Amount field is required');
            return false;
        }
        if ($this->hasPayoutToken() && $this->hasPayoutDestinationData()) {
            $this->setValidationError('Both payoutToken and payoutDestinationData values are specified');
            return false;
        }

        if (!$this->hasPayoutToken() && !$this->hasPayoutDestinationData()) {
            $this->setValidationError('Both payoutToken and payoutDestinationData values are not specified');
            return false;
        }

        return true;
    }

    /**
     * Возвращает билдер объектов запросов создания выплаты
     * @return CreatePayoutRequestBuilder Инстанс билдера объектов запросов
     */
    public static function builder()
    {
        return new CreatePayoutRequestBuilder();
    }
}
