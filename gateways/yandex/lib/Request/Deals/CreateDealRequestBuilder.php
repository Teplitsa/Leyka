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

use YooKassa\Common\AbstractRequest;
use YooKassa\Common\AbstractRequestBuilder;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Model\Metadata;

/**
 * Класс билдера объектов запросов к API на создание платежа
 *
 * @todo: @example 02-builder.php 11 78 Пример использования билдера
 *
 * @package YooKassa
 */
class CreateDealRequestBuilder extends AbstractRequestBuilder
{
    /**
     * Собираемый объект запроса
     * @var CreateDealRequest
     */
    protected $currentObject;

    /**
     * Инициализирует объект запроса, который в дальнейшем будет собираться билдером
     * @return CreateDealRequest Инстанс собираемого объекта запроса к API
     */
    protected function initCurrentObject()
    {
        return new CreateDealRequest();
    }

    /**
     * Устанавливает тип сделки
     * @param string $value Тип сделки
     * @return CreateDealRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Генерируется если переданный аргумент не является строкой
     * @throws InvalidPropertyValueException Генерируется если переданный аргумент не из списка DealType

     */
    public function setType($value)
    {
        $this->currentObject->setType($value);
        return $this;
    }

    /**
     * Устанавливает момент перечисления вам вознаграждения платформы
     * @param string $value Момент перечисления вам вознаграждения платформы
     * @return CreateDealRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Генерируется если переданный аргумент не является строкой
     * @throws InvalidPropertyValueException Генерируется если переданный аргумент не из списка FeeMoment
     */
    public function setFeeMoment($value)
    {
        $this->currentObject->setFeeMoment($value);
        return $this;
    }

    /**
     * Устанавливает метаданные, привязанные к платежу
     * @param Metadata|array|null $value Метаданные платежа, устанавливаемые мерчантом
     * @return CreateDealRequestBuilder Инстанс текущего билдера
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
     * @return CreateDealRequestBuilder Инстанс текущего билдера
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
     * @param array|null $options
     * @return CreateDealRequest|AbstractRequest
     */
    public function build(array $options = null)
    {
        return parent::build($options);
    }
}
