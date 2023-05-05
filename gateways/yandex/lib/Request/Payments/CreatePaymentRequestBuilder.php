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

namespace YooKassa\Request\Payments;

use YooKassa\Common\AbstractPaymentRequest;
use YooKassa\Common\AbstractPaymentRequestBuilder;
use YooKassa\Common\AbstractRequest;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Common\Exceptions\InvalidRequestException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\Airline;
use YooKassa\Model\AirlineInterface;
use YooKassa\Model\ConfirmationAttributes\AbstractConfirmationAttributes;
use YooKassa\Model\ConfirmationAttributes\ConfirmationAttributesFactory;
use YooKassa\Model\Deal\PaymentDealInfo;
use YooKassa\Model\Metadata;
use YooKassa\Model\Payment;
use YooKassa\Model\PaymentData\AbstractPaymentData;
use YooKassa\Model\PaymentData\PaymentDataFactory;
use YooKassa\Model\Recipient;
use YooKassa\Model\RecipientInterface;

/**
 * Класс билдера объектов запросов к API на создание платежа
 *
 * @example 02-builder.php 11 78 Пример использования билдера
 *
 * @package YooKassa
 */
class CreatePaymentRequestBuilder extends AbstractPaymentRequestBuilder
{
    /**
     * Собираемый объект запроса
     * @var CreatePaymentRequest
     */
    protected $currentObject;

    /**
     * @var Recipient Получатель платежа
     */
    private $recipient;

    /**
     * @var PaymentDataFactory Фабрика методов проведения платежей
     */
    private $paymentDataFactory;

    /**
     * @var ConfirmationAttributesFactory Фабрика объектов методов подтверждения платежей
     */
    private $confirmationFactory;

    /**
     * @var Airline Длинная запись
     */
    private $airline;

    /**
     * Инициализирует объект запроса, который в дальнейшем будет собираться билдером
     * @return CreatePaymentRequest Инстанс собираемого объекта запроса к API
     */
    protected function initCurrentObject()
    {
        parent::initCurrentObject();

        $request = new CreatePaymentRequest();

        $this->recipient = new Recipient();
        $this->airline = new Airline();

        return $request;
    }

    /**
     * Устанавливает идентификатор магазина получателя платежа
     * @param string $value Идентификатор магазина
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws EmptyPropertyValueException Выбрасывается если было передано пустое значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если было передано не строковое значение
     */
    public function setAccountId($value)
    {
        $this->recipient->setAccountId($value);
        return $this;
    }

    /**
     * Устанавливает идентификатор шлюза
     * @param string $value Идентификатор шлюза
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws EmptyPropertyValueException Выбрасывается если было передано пустое значение
     * @throws InvalidPropertyValueTypeException Выбрасывается если было передано не строковое значение
     */
    public function setGatewayId($value)
    {
        $this->recipient->setGatewayId($value);
        return $this;
    }

    /**
     * Устанавливает получателя платежа из объекта или ассоциативного массива
     * @param RecipientInterface|array $value Получатель платежа
     * @return CreatePaymentRequestBuilder
     * @throws InvalidPropertyValueTypeException Выбрасывается если передан аргумент не валидного типа
     */
    public function setRecipient($value)
    {
        if (is_array($value)) {
            $this->recipient->fromArray($value);
        } elseif ($value instanceof RecipientInterface) {
            $this->recipient->setAccountId($value->getAccountId());
            $this->recipient->setGatewayId($value->getGatewayId());
        } else {
            throw new InvalidPropertyValueTypeException('Invalid recipient value', 0, 'recipient', $value);
        }
        return $this;
    }

    /**
     * Устанавливает информацию об авиабилетах
     * @param AirlineInterface|array $value Объект данных длинной записи или ассоциативный массив с данными
     *
     * @return CreatePaymentRequestBuilder
     */
    public function setAirline($value)
    {
        if (is_array($value)) {
            $this->airline->fromArray($value);
        } elseif ($value instanceof AirlineInterface) {
            $this->airline = clone $value;
        } else {
            throw new InvalidPropertyValueTypeException('Invalid receipt value type', 0, 'receipt', $value);
        }


        return $this;
    }

    /**
     * Устанавливает одноразовый токен для проведения оплаты
     * @param string $value Одноразовый токен для проведения оплаты
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение превышает допустимую длину
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является строкой
     */
    public function setPaymentToken($value)
    {
        $this->currentObject->setPaymentToken($value);
        return $this;
    }

    /**
     * Устанавливает идентификатор записи о сохранённых данных покупателя
     * @param string $value Идентификатор записи о сохраненных платежных данных покупателя
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Генерируется если переданные значение не является строкой или null
     */
    public function setPaymentMethodId($value)
    {
        $this->currentObject->setPaymentMethodId($value);
        return $this;
    }

    /**
     * Устанавливает объект с информацией для создания метода оплаты
     * @param AbstractPaymentData|string|array|null $value Объект создания метода оплаты или null
     * @param array $options Настройки способа оплаты в виде ассоциативного массива
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если был передан объект невалидного типа
     */
    public function setPaymentMethodData($value, array $options = null)
    {
        if (is_string($value) && $value !== '') {
            if (empty($options)) {
                $value = $this->getPaymentDataFactory()->factory($value);
            } else {
                $value = $this->getPaymentDataFactory()->factoryFromArray($options, $value);
            }
        } elseif (is_array($value)) {
            $value = $this->getPaymentDataFactory()->factoryFromArray($value);
        }
        $this->currentObject->setPaymentMethodData($value);
        return $this;
    }

    /**
     * Устанавливает способ подтверждения платежа
     * @param AbstractConfirmationAttributes|string|array|null $value Способ подтверждения платежа
     * @param array|null $options Настройки способа подтверждения платежа в виде массива
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является объектом типа
     * AbstractConfirmationAttributes или null
     */
    public function setConfirmation($value, array $options = null)
    {
        if (is_string($value) && $value !== '') {
            if (empty($options)) {
                $value = $this->getConfirmationFactory()->factory($value);
            } else {
                $value = $this->getConfirmationFactory()->factoryFromArray($options, $value);
            }
        } elseif (is_array($value)) {
            $value = $this->getConfirmationFactory()->factoryFromArray($value);
        }
        $this->currentObject->setConfirmation($value);
        return $this;
    }

    /**
     * Устанавливает флаг сохранения платёжных данных. Значение true инициирует создание многоразового payment_method.
     * @param bool $value Сохранить платежные данные для последующего использования
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Генерируется если переданный аргумент не кастится в bool
     */
    public function setSavePaymentMethod($value)
    {
        $this->currentObject->setSavePaymentMethod($value);
        return $this;
    }

    /**
     * Устанавливает флаг автоматического принятия поступившей оплаты
     * @param bool $value Автоматически принять поступившую оплату
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Генерируется если переданный аргумент не кастится в bool

     */
    public function setCapture($value)
    {
        $this->currentObject->setCapture($value);
        return $this;
    }

    /**
     * Устанавливает IP адрес покупателя
     * @param string $value IPv4 или IPv6-адрес покупателя
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент не является строкой
     */
    public function setClientIp($value)
    {
        $this->currentObject->setClientIp($value);
        return $this;
    }

    /**
     * Устанавливает метаданные, привязанные к платежу
     * @param Metadata|array|null $value Метаданные платежа, устанавливаемые мерчантом
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданные данные не удалось интерпретировать как
     * метаданные платежа
     */
    public function setMetadata($value)
    {
        $this->currentObject->setMetadata($value);
        return $this;
    }

    /**
     * Устанавливает описание транзакции
     * @param string $value Описание транзакции
     * @return CreatePaymentRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение превышает допустимую длину
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является строкой
     */
    public function setDescription($value)
    {
        $this->currentObject->setDescription($value);
        return $this;
    }

    /**
     * Устанавливает данные о сделке, в составе которой проходит платеж.
     * @param PaymentDealInfo|array|null $value Данные о сделке, в составе которой проходит платеж
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданные данные не удалось интерпретировать как метаданные платежа
     */
    public function setDeal($value)
    {
        $this->currentObject->setDeal($value);
        return $this;
    }

    /**
     * Устанавливает идентификатор покупателя в вашей системе
     * @param string $value Идентификатор покупателя в вашей системе, например электронная почта или номер телефона. Не более 200 символов
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент не является строкой
     */
    public function setMerchantCustomerId($value)
    {
        $this->currentObject->setMerchantCustomerId($value);
        return $this;
    }

    /**
     * Строит и возвращает объект запроса для отправки в API ЮKassa
     * @param array|null $options Массив параметров для установки в объект запроса
     * @return CreatePaymentRequestInterface|AbstractPaymentRequest|AbstractRequest Инстанс объекта запроса
     *
     * @throws InvalidRequestException Выбрасывается если собрать объект запроса не удалось
     */
    public function build(array $options = null)
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }
        $gatewayId = $this->recipient->getGatewayId();
        if (!empty($gatewayId)) {
            $this->currentObject->setRecipient($this->recipient);
        }
        if ($this->receipt->notEmpty()) {
            $this->currentObject->setReceipt($this->receipt);
        }
        if($this->airline->notEmpty()){
            $this->currentObject->setAirline($this->airline);
        }
        $this->currentObject->setAmount($this->amount);
        $this->currentObject->setTransfers($this->transfers);

        return parent::build();
    }

    /**
     * Возвращает фабрику методов проведения платежей
     * @return PaymentDataFactory Фабрика методов проведения платежей
     */
    protected function getPaymentDataFactory()
    {
        if ($this->paymentDataFactory === null) {
            $this->paymentDataFactory = new PaymentDataFactory();
        }
        return $this->paymentDataFactory;
    }

    /**
     * Возвращает фабрику для создания методов подтверждения платежей
     * @return ConfirmationAttributesFactory Фабрика объектов методов подтверждения платежей
     */
    protected function getConfirmationFactory()
    {
        if ($this->confirmationFactory === null) {
            $this->confirmationFactory = new ConfirmationAttributesFactory();
        }
        return $this->confirmationFactory;
    }
}
