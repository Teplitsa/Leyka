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

namespace YooKassa\Request\Receipts;

use YooKassa\Common\AbstractRequest;
use YooKassa\Common\AbstractRequestBuilder;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;

/**
 * Класс билдера объектов запросов к API списка чеков
 *
 * @package YooKassa
 */
class ReceiptsRequestBuilder extends AbstractRequestBuilder
{
    /**
     * @var ReceiptsRequest Инстанс собираемого объекта запроса
     */
    protected $currentObject;

    /**
     * Инициализирует новый инстанс собираемого объекта
     * @return ReceiptsRequest Инстанс собираемого запроса
     */
    protected function initCurrentObject()
    {
        return new ReceiptsRequest();
    }

    /**
     * Устанавливает идентификатор возврата
     * @param string $value Идентификатор возврата, который ищется в API
     * @return ReceiptsRequestBuilder Инстанс текущего объекта билдера
     *
     * @throws InvalidPropertyValueException Выбрасывается если длина переданного значения не равна 36
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setRefundId($value)
    {
        $this->currentObject->setRefundId($value);
        return $this;
    }

    /**
     * Устанавливает идентификатор платежа или null, если требуется его удалить
     * @param string|null $value Идентификатор платежа
     * @return ReceiptsRequestBuilder Инстанс текущего объекта билдера
     *
     * @throws InvalidPropertyValueException Выбрасывается если длина переданной строки не равна 36 символам
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setPaymentId($value)
    {
        $this->currentObject->setPaymentId($value);
        return $this;
    }

    /**
     * Устанавливает статус выбираемых чеков
     * @param string $value Статус выбираемых платежей или null, чтобы удалить значение
     * @return ReceiptsRequestBuilder Инстанс текущего объекта билдера
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
     * Устанавливает ограничение количества объектов чеков
     * @param string $value Ограничение количества объектов чеков или null, чтобы удалить значение
     * @return ReceiptsRequestBuilder Инстанс текущего билдера
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод было передана не целое число
     */
    public function setLimit($value)
    {
        $this->currentObject->setLimit($value);
        return $this;
    }

    /**
     * Устанавливает токен следующей страницы выборки
     * @param string $value Токен следующей страницы выборки или null, чтобы удалить значение
     * @return ReceiptsRequestBuilder Инстанс текущего объекта билдера
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setCursor($value)
    {
        $this->currentObject->setCursor($value);
        return $this;
    }

    /**
     * Устанавливает дату создания от которой выбираются чеки
     * @param \DateTime|string|int|null $value Время создания, от (не включая) или null, чтобы удалить значение
     * @return ReceiptsRequestBuilder Инстанс текущего объекта билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|\Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtGt($value)
    {
        $this->currentObject->setCreatedAtGt($value);
        return $this;
    }

    /**
     * Устанавливает дату создания от которой выбираются чеки
     * @param \DateTime|string|int|null $value Время создания, от (включительно) или null, чтобы удалить значение
     * @return ReceiptsRequestBuilder Инстанс текущего объекта билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|\Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtGte($value)
    {
        $this->currentObject->setCreatedAtGte($value);
        return $this;
    }

    /**
     * Устанавливает дату создания до которой выбираются чеки
     * @param \DateTime|string|int|null $value Время создания, до (не включая) или null, чтобы удалить значение
     * @return ReceiptsRequestBuilder Инстанс текущего объекта билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|\Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtLt($value)
    {
        $this->currentObject->setCreatedAtLt($value);
        return $this;
    }

    /**
     * Устанавливает дату создания до которой выбираются чеки
     * @param \DateTime|string|int|null $value Время создания, до (включительно) или null, чтобы удалить значение
     * @return ReceiptsRequestBuilder Инстанс текущего объекта билдера
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|\Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtLte($value)
    {
        $this->currentObject->setCreatedAtLte($value);
        return $this;
    }

    /**
     * Собирает и возвращает объект запроса списка чеков магазина
     * @param array|null $options Массив с настройками запроса
     * @return ReceiptsRequestInterface|AbstractRequest Инстанс объекта запроса к API для получения списка чеков магазина
     */
    public function build(array $options = null)
    {
        return parent::build($options);
    }
}
