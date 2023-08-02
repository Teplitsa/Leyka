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

use YooKassa\Common\AbstractPaymentRequestBuilder;
use YooKassa\Common\AbstractRequest;
use YooKassa\Common\AbstractRequestBuilder;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Common\Exceptions\InvalidRequestException;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\Metadata;
use YooKassa\Model\Payout\AbstractPayoutDestination;
use YooKassa\Model\Deal\PayoutDealInfo;

/**
 * Класс билдера объектов запросов к API на создание платежа
 *
 * @todo: @example 02-builder.php 11 78 Пример использования билдера
 *
 * @package YooKassa
 */
class CreatePayoutRequestBuilder extends AbstractRequestBuilder
{
    /**
     * Собираемый объект запроса
     * @var CreatePayoutRequest
     */
    protected $currentObject;

    /**
     * Инициализирует объект запроса, который в дальнейшем будет собираться билдером
     * @return CreatePayoutRequest Инстанс собираемого объекта запроса к API
     */
    protected function initCurrentObject()
    {
        return new CreatePayoutRequest();
    }

    /**
     * Устанавливает сумму
     *
     * @param AmountInterface|array|string $value Сумма выплаты
     *
     * @return self Инстанс билдера запросов
     */
    public function setAmount($value)
    {
        $this->currentObject->setAmount($value);

        return $this;
    }

    /**
     * Устанавливает одноразовый токен для проведения выплаты
     * @param string $value Одноразовый токен для проведения выплаты
     * @return CreatePayoutRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданное значение не является строкой
     */
    public function setPayoutToken($value)
    {
        $this->currentObject->setPayoutToken($value);
        return $this;
    }

    /**
     * Устанавливает объект с информацией для создания метода оплаты
     * @param AbstractPayoutDestination|array|null $value Объект создания метода оплаты или null
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если был передан объект невалидного типа
     */
    public function setPayoutDestinationData($value)
    {
        $this->currentObject->setPayoutDestinationData($value);
        return $this;
    }

    /**
     * Устанавливает сделку, в рамках которой нужно провести выплату
     * @param PayoutDealInfo|array $value Сделка, в рамках которой нужно провести выплату
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если был передан объект невалидного типа
     */
    public function setDeal($value)
    {
        $this->currentObject->setDeal($value);
        return $this;
    }

    /**
     * Устанавливает метаданные, привязанные к платежу
     * @param Metadata|array|null $value Метаданные платежа, устанавливаемые мерчантом
     * @return CreatePayoutRequestBuilder Инстанс текущего билдера
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
     * @return CreatePayoutRequestBuilder Инстанс текущего билдера
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
     * Строит и возвращает объект запроса для отправки в API ЮKassa
     * @param array|null $options Массив параметров для установки в объект запроса
     * @return CreatePayoutRequestInterface|CreatePayoutRequest|AbstractRequest Инстанс объекта запроса
     *
     * @throws InvalidRequestException Выбрасывается если собрать объект запроса не удалось
     */
    public function build(array $options = null)
    {
        return parent::build($options);
    }

}
