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

use DateTime;
use Exception;
use YooKassa\Common\AbstractRequest;
use YooKassa\Common\AbstractRequestBuilder;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;

/**
 * Класс билдера запросов к API для получения списка сделок магазина
 *
 * @package YooKassa
 */
class DealsRequestBuilder extends AbstractRequestBuilder
{
    /**
     * @var DealsRequest Собираемый объект запроса списка сделок магазина
     */
    protected $currentObject;

    /**
     * Возвращает новый объект запроса для получения списка сделок, который в дальнейшем будет собираться в билдере
     * @return DealsRequest Объект запроса списка сделок магазина
     */
    protected function initCurrentObject()
    {
        return new DealsRequest();
    }

    /**
     * Устанавливает ограничение количества объектов сделки
     * @param string $value Ограничение количества объектов сделки или null, чтобы удалить значение
     * @return DealsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод было передана не целое число
     */
    public function setLimit($value)
    {
        $this->currentObject->setLimit($value);
        return $this;
    }

    /**
     * Устанавливает страница выдачи результатов
     * @param string|null $value Страница выдачи результатов или null, чтобы удалить значение
     * @return DealsRequestBuilder Инстанс текущего билдера
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
     * @return DealsRequestBuilder Инстанс текущего билдера
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
     * @return DealsRequestBuilder Инстанс текущего билдера
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
     * @return DealsRequestBuilder Инстанс текущего билдера
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
     * @return DealsRequestBuilder Инстанс текущего билдера
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
     * Устанавливает дату автоматического закрытия от которой выбираются платежи
     * @param DateTime|string|int|null $value Время автоматического закрытия, до (включительно) или null, чтобы удалить значение
     * @return DealsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setExpiresAtGt($value)
    {
        $this->currentObject->setExpiresAtGt($value);
        return $this;
    }

    /**
     * Устанавливает дату автоматического закрытия от которой выбираются платежи
     * @param DateTime|string|int|null $value Время автоматического закрытия, от (включительно) или null, чтобы удалить значение
     * @return DealsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setExpiresAtGte($value)
    {
        $this->currentObject->setExpiresAtGte($value);
        return $this;
    }

    /**
     * Устанавливает дату автоматического закрытия до которой выбираются платежи
     * @param DateTime|string|int|null $value Время автоматического закрытия, до (включительно) или null, чтобы удалить значение
     * @return DealsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setExpiresAtLt($value)
    {
        $this->currentObject->setExpiresAtLt($value);
        return $this;
    }

    /**
     * Устанавливает дату автоматического закрытия до которой выбираются платежи
     * @param DateTime|string|int|null $value Время автоматического закрытия, до (включительно) или null, чтобы удалить значение
     * @return DealsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setExpiresAtLte($value)
    {
        $this->currentObject->setExpiresAtLte($value);
        return $this;
    }

    /**
     * Устанавливает статус выбираемых сделок
     * @param string $value Статус выбираемых сделок или null, чтобы удалить значение
     * @return DealsRequestBuilder Инстанс текущего билдера
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
     * Устанавливает фильтр по описанию выбираемых сделок
     * @param string $value Фильтр по описанию выбираемых сделок или null, чтобы удалить значение
     * @return DealsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение не является валидным
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setFullTextSearch($value)
    {
        $this->currentObject->setFullTextSearch($value);
        return $this;
    }

    /**
     * Собирает и возвращает объект запроса списка сделок магазина
     * @param array|null $options Массив с настройками запроса
     * @return AbstractRequest|DealsRequest Инстанс объекта запроса к API для получения списка сделок магазина
     */
    public function build(array $options = null)
    {
        return parent::build($options);
    }
}
