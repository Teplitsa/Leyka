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

use DateTime;
use Exception;
use YooKassa\Common\AbstractRequest;
use YooKassa\Common\AbstractRequestBuilder;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;

/**
 * Класс билдера запросов к API для получения списка платежей магазина
 *
 * @package YooKassa
 */
class PaymentsRequestBuilder extends AbstractRequestBuilder
{
    /**
     * @var PaymentsRequest Собираемый объект запроса списка платежей магазина
     */
    protected $currentObject;

    /**
     * Возвращает новый объект запроса для получения списка платежей, который в дальнейшем будет собираться в билдере
     * @return PaymentsRequest Объект запроса списка платежей магазина
     */
    protected function initCurrentObject()
    {
        return new PaymentsRequest();
    }

    /**
     * Устанавливает страница выдачи результатов
     * @param string|null $value Страница выдачи результатов или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setCursor($value)
    {
        $this->currentObject->setCursor($value);
        return $this;
    }

    /**
     * Устанавливает дату создания от которой выбираются платежи
     * @param DateTime|string|int|null $value Время создания, от (не включая) или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtGt($value)
    {
        $this->currentObject->setCreatedAtGt($value);
        return $this;
    }

    /**
     * Устанавливает дату создания от которой выбираются платежи
     * @param DateTime|string|int|null $value Время создания, от (включительно) или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtGte($value)
    {
        $this->currentObject->setCreatedAtGte($value);
        return $this;
    }

    /**
     * Устанавливает дату создания до которой выбираются платежи
     * @param DateTime|string|int|null $value Время создания, до (не включая) или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtLt($value)
    {
        $this->currentObject->setCreatedAtLt($value);
        return $this;
    }

    /**
     * Устанавливает дату создания до которой выбираются платежи
     * @param DateTime|string|int|null $value Время создания, до (включительно) или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtLte($value)
    {
        $this->currentObject->setCreatedAtLte($value);
        return $this;
    }

    /**
     * Устанавливает дату подтверждения от которой выбираются платежи
     * @param DateTime|string|int|null $value Время создания, до (включительно) или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCapturedAtGt($value)
    {
        $this->currentObject->setCapturedAtGt($value);
        return $this;
    }

    /**
     * Устанавливает дату подтверждения от которой выбираются платежи
     * @param DateTime|string|int|null $value Время подтверждения, от (включительно) или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCapturedAtGte($value)
    {
        $this->currentObject->setCapturedAtGte($value);
        return $this;
    }

    /**
     * Устанавливает дату подтверждения до которой выбираются платежи
     * @param DateTime|string|int|null $value Время подтверждения, до (включительно) или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCapturedAtLt($value)
    {
        $this->currentObject->setCapturedAtLt($value);
        return $this;
    }

    /**
     * Устанавливает дату подтверждения до которой выбираются платежи
     * @param DateTime|string|int|null $value Время подтверждения, до (включительно) или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCapturedAtLte($value)
    {
        $this->currentObject->setCapturedAtLte($value);
        return $this;
    }

    /**
     * Устанавливает ограничение количества объектов платежа
     * @param string $value Ограничение количества объектов платежа или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод было передана не целое число
     */
    public function setLimit($value)
    {
        $this->currentObject->setLimit($value);
        return $this;
    }

    /**
     * Устанавливает статус выбираемых платежей
     * @param string $value Статус выбираемых платежей или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение не является валидным статусом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setStatus($value)
    {
        $this->currentObject->setStatus($value);
        return $this;
    }

    /**
     * Устанавливает платежный метод выбираемых платежей
     * @param string $value Платежный метод выбираемых платежей или null, чтобы удалить значение
     * @return PaymentsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение не является валидным статусом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setPaymentMethod($value)
    {
        $this->currentObject->setPaymentMethod($value);
        return $this;
    }

    /**
     * Собирает и возвращает объект запроса списка платежей магазина
     * @param array|null $options Массив с настройками запроса
     * @return AbstractRequest Инстанс объекта запроса к API для получения списка платежей магазина
     */
    public function build(array $options = null)
    {
        return parent::build($options);
    }
}
