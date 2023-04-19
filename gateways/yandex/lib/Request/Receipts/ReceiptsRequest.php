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

use Exception;
use YooKassa\Common\AbstractRequest;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\RefundStatus;

/**
 * Класс объекта запроса к API списка возвратов магазина
 *
 * @package YooKassa
 *
 * @property \DateTime $createdAtGte Время создания, от (включительно)
 * @property \DateTime $createdAtGt Время создания, от (не включая)
 * @property \DateTime $createdAtLte Время создания, до (включительно)
 * @property \DateTime $createdAtLt Время создания, до (не включая)
 * @property string $paymentId Идентификатор платежа
 * @property string $refundId Идентификатор возврата
 * @property string $status Статус возврата
 * @property integer|null $limit Ограничение количества объектов возврата, отображаемых на одной странице выдачи
 * @property string $cursor Токен для получения следующей страницы выборки
 */
class ReceiptsRequest extends AbstractRequest implements ReceiptsRequestInterface
{
    /** Максимальное количество объектов чеков в выборке */
    const MAX_LIMIT_VALUE = 100;

    /**
     * @var \DateTime Время создания, от (включительно)
     */
    private $_createdAtGte;

    /**
     * @var \DateTime Время создания, от (не включая)
     */
    private $_createdAtGt;

    /**
     * @var \DateTime Время создания, до (включительно)
     */
    private $_createdAtLte;

    /**
     * @var \DateTime Время создания, до (не включая)
     */
    private $_createdAtLt;

    /**
     * @var string Идентификатор платежа
     */
    private $_paymentId;

    /**
     * @var string Идентификатор возврата
     */
    private $_refundId;

    /**
     * @var string Статус возврата
     */
    private $_status;

    /**
     * @var string Ограничение количества объектов платежа
     */
    private $_limit;

    /**
     * @var string Токен для получения следующей страницы выборки
     */
    private $_cursor;

    /**
     * Возвращает идентификатор возврата
     * @return string Идентификатор возврата
     */
    public function getRefundId()
    {
        return $this->_refundId;
    }

    /**
     * Проверяет, был ли установлен идентификатор возврата
     * @return bool True если идентификатор возврата был установлен, false если не был
     */
    public function hasRefundId()
    {
        return !empty($this->_refundId);
    }

    /**
     * Устанавливает идентификатор возврата
     * @param string $value Идентификатор возврата, который ищется в API
     */
    public function setRefundId($value)
    {
        if ($value === null || $value === '') {
            $this->_refundId = null;
        } elseif (TypeCast::canCastToString($value)) {
            $length = mb_strlen((string)$value, 'utf-8');
            if ($length != 36) {
                throw new InvalidPropertyValueException(
                    'Invalid payment id value in ReceiptsRequest', 0, 'ReceiptsRequest.refundId', $value
                );
            }
            $this->_refundId = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payment id value type in ReceiptsRequest', 0, 'ReceiptsRequest.refundId', $value
            );
        }
    }

    /**
     * Возвращает идентификатор платежа если он задан или null
     * @return string|null Идентификатор платежа
     */
    public function getPaymentId()
    {
        return $this->_paymentId;
    }

    /**
     * Проверяет, был ли задан идентификатор платежа
     * @return bool True если идентификатор был задан, false если нет
     */
    public function hasPaymentId()
    {
        return !empty($this->_paymentId);
    }

    /**
     * Устанавливает идентификатор платежа или null, если требуется его удалить
     * @param string|null $value Идентификатор платежа
     *
     * @throws InvalidPropertyValueException Выбрасывается если длина переданной строки не равна 36 символам
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setPaymentId($value)
    {
        if ($value === null || $value === '') {
            $this->_paymentId = null;
        } elseif (TypeCast::canCastToString($value)) {
            $length = mb_strlen((string)$value, 'utf-8');
            if ($length != 36) {
                throw new InvalidPropertyValueException(
                    'Invalid payment id value in ReceiptsRequest', 0, 'ReceiptsRequest.paymentId', $value
                );
            }
            $this->_paymentId = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payment id value type in ReceiptsRequest', 0, 'ReceiptsRequest.paymentId', $value
            );
        }
    }

    /**
     * Возвращает дату создания от которой будут возвращены возвраты или null, если дата не была установлена
     * @return \DateTime|null Время создания, от (включительно)
     */
    public function getCreatedAtGte()
    {
        return $this->_createdAtGte;
    }

    /**
     * Проверяет, была ли установлена дата создания от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtGte()
    {
        return !empty($this->_createdAtGte);
    }

    /**
     * Устанавливает дату создания от которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время создания, от (включительно) или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtGte($value)
    {
        if ($value === null || $value === '') {
            $this->_createdAtGte = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid created_gte value in ReceiptsRequest', 0, 'ReceiptsRequest.createdAtGte'
                );
            }
            $this->_createdAtGte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid created_gte value type in ReceiptsRequest', 0, 'ReceiptsRequest.createdAtGte'
            );
        }
    }

    /**
     * Возвращает дату создания от которой будут возвращены возвраты или null, если дата не была установлена
     * @return \DateTime|null Время создания, от (не включая)
     */
    public function getCreatedAtGt()
    {
        return $this->_createdAtGt;
    }

    /**
     * Проверяет, была ли установлена дата создания от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtGt()
    {
        return !empty($this->_createdAtGt);
    }

    /**
     * Устанавливает дату создания от которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время создания, от (не включая) или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtGt($value)
    {
        if ($value === null || $value === '') {
            $this->_createdAtGt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid created_gt value in ReceiptsRequest', 0, 'ReceiptsRequest.createdAtGt'
                );
            }
            $this->_createdAtGt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid created_gt value type in ReceiptsRequest', 0, 'ReceiptsRequest.createdAtGt'
            );
        }
    }

    /**
     * Возвращает дату создания до которой будут возвращены возвраты или null, если дата не была установлена
     * @return \DateTime|null Время создания, до (включительно)
     */
    public function getCreatedAtLte()
    {
        return $this->_createdAtLte;
    }

    /**
     * Проверяет, была ли установлена дата создания до которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtLte()
    {
        return !empty($this->_createdAtLte);
    }

    /**
     * Устанавливает дату создания до которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время создания, до (включительно) или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtLte($value)
    {
        if ($value === null || $value === '') {
            $this->_createdAtLte = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid created_lte value in ReceiptsRequest', 0, 'ReceiptsRequest.createdLte'
                );
            }
            $this->_createdAtLte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid created_lte value type in ReceiptsRequest', 0, 'ReceiptsRequest.createdLte'
            );
        }
    }

    /**
     * Возвращает дату создания до которой будут возвращены возвраты или null, если дата не была установлена
     * @return \DateTime|null Время создания, до (не включая)
     */
    public function getCreatedAtLt()
    {
        return $this->_createdAtLt;
    }

    /**
     * Проверяет, была ли установлена дата создания до которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtLt()
    {
        return !empty($this->_createdAtLt);
    }

    /**
     * Устанавливает дату создания до которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время создания, до (не включая) или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedAtLt($value)
    {
        if ($value === null || $value === '') {
            $this->_createdAtLt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid created_lt value in ReceiptsRequest', 0, 'ReceiptsRequest.createdLt'
                );
            }
            $this->_createdAtLt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid created_lt value type in ReceiptsRequest', 0, 'ReceiptsRequest.createdLt'
            );
        }
    }

    /**
     * Возвращает статус выбираемых возвратов или null, если он до этого не был установлен
     * @return string|null Статус выбираемых возвратов
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Проверяет, был ли установлен статус выбираемых возвратов
     * @return bool True если статус был установлен, false если нет
     */
    public function hasStatus()
    {
        return !empty($this->_status);
    }

    /**
     * Устанавливает статус выбираемых возвратов
     * @param string $value Статус выбираемых платежей или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение не является валидным статусом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setStatus($value)
    {
        if ($value === null || $value === '') {
            $this->_status = null;
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (!RefundStatus::valueExists((string)$value)) {
                throw new InvalidPropertyValueException(
                    'Invalid status value in ReceiptsRequest', 0, 'ReceiptsRequest.status', $value
                );
            } else {
                $this->_status = (string)$value;
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid status value type in ReceiptsRequest', 0, 'ReceiptsRequest.status', $value
            );
        }
    }

    /**
     * Возвращает токен для получения следующей страницы выборки
     * @return string|null Токен для получения следующей страницы выборки
     */
    public function getCursor()
    {
        return $this->_cursor;
    }

    /**
     * Проверяет, был ли установлен токен следующей страницы
     * @return bool True если токен был установлен, false если нет
     */
    public function hasCursor()
    {
        return !empty($this->_cursor);
    }

    /**
     * Устанавливает токен следующей страницы выборки
     * @param string $value Токен следующей страницы выборки или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setCursor($value)
    {
        if ($value === null || $value === '') {
            $this->_cursor = null;
        } elseif (TypeCast::canCastToString($value)) {
            $this->_cursor = (string) $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid status value type in ReceiptsRequest', 0, 'ReceiptsRequest.cursor', $value
            );
        }
    }

    /**
     * Ограничение количества объектов платежа
     * @return integer|null Ограничение количества объектов платежа
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * Проверяет, было ли установлено ограничение количества объектов платежа
     * @return bool True если было установлено, false если нет
     */
    public function hasLimit()
    {
        return $this->_limit !== null;
    }

    /**
     * Устанавливает ограничение количества объектов платежа
     * @param integer|null $value Ограничение количества объектов платежа или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается, если в метод было передано не целое число
     */
    public function setLimit($value)
    {
        if ($value === null || $value === '') {
            $this->_limit = null;
        } elseif (is_int($value)) {
            if ($value < 0 || $value > self::MAX_LIMIT_VALUE) {
                throw new InvalidPropertyValueException(
                    'Invalid limit value in ReceiptsRequest', 0, 'ReceiptsRequest.limit', $value
                );
            }
            $this->_limit = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid limit value type in ReceiptsRequest', 0, 'ReceiptsRequest.limit', $value
            );
        }
    }

    /**
     * Проверяет валидность текущего объекта запроса
     * @return bool True если объект валиден, false если нет
     */
    public function validate()
    {
        return true;
    }

    /**
     * Возвращает инстанс билдера объектов запросов списка возвратов магазина
     * @return ReceiptsRequestBuilder Билдер объектов запросов списка возвратов
     */
    public static function builder()
    {
        return new ReceiptsRequestBuilder();
    }
}
