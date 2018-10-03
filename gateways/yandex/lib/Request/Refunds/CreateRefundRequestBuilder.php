<?php

/**
 * The MIT License
 *
 * Copyright (c) 2017 NBCO Yandex.Money LLC
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

namespace YandexCheckout\Request\Refunds;

use YandexCheckout\Common\AbstractPaymentRequestBuilder;
use YandexCheckout\Common\Exceptions\EmptyPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException;

/**
 * Класс билдера запросов к API на создание возврата средств
 *
 * @package YandexCheckout\Request\Refunds
 */
class CreateRefundRequestBuilder extends AbstractPaymentRequestBuilder
{
    /**
     * @var CreateRefundRequest Собираемый объет запроса к API
     */
    protected $currentObject;

    /**
     * Возвращает новый объект для сборки
     * @return CreateRefundRequest Собираемый объет запроса к API
     */
    protected function initCurrentObject()
    {
        parent::initCurrentObject();
        $request = new CreateRefundRequest();
        return $request;
    }

    /**
     * Устанавливает айди платежа для которого создаётся возврат
     * @param string $value Айди платежа
     * @return CreateRefundRequestBuilder Инстанс текущего билдера
     *
     * @throws EmptyPropertyValueException Выбрасывается если передано пустое значение айди платежа
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение является строкой, но не является
     * валидным значением айди платежа
     * @throws InvalidPropertyValueTypeException Выбрасывается если передано значение не валидного типа
     */
    public function setPaymentId($value)
    {
        $this->currentObject->setPaymentId($value);
        return $this;
    }

    /**
     * Устанавливает комментарий к возврату
     * @param string $value Комментарий к возврату
     * @return CreateRefundRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданная строка длинее 250 символов
     * @throws InvalidPropertyValueTypeException Выбрасывается если была передана не строка
     */
    public function setComment($value)
    {
        $this->currentObject->setComment($value);
        return $this;
    }

    /**
     * Строит объект запроса к API
     * @param array|null $options Устаналвиваемые параметры запроса
     * @return CreateRefundRequestInterface Инстанс сгенерированного объекта запроса к API
     */
    public function build(array $options = null)
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }
        $this->currentObject->setAmount($this->amount);
        if ($this->receipt->notEmpty()) {
            $this->currentObject->setReceipt($this->receipt);
        }
        return parent::build();
    }
}