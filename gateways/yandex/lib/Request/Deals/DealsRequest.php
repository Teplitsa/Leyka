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

use Exception;
use YooKassa\Common\AbstractRequest;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\Deal\DealStatus;
use YooKassa\Model\SafeDeal;

/**
 * Класс объекта запроса к API для получения списка сделок магазина
 *
 * @property string|null $cursor Страница выдачи результатов, которую необходимо отобразить
 * @property integer|null $limit Ограничение количества объектов платежа, отображаемых на одной странице выдачи
 * @property \DateTime|null $createdAtGte Время создания, от (включительно)
 * @property \DateTime|null $createdAtGt Время создания, от (не включая)
 * @property \DateTime|null $createdAtLte Время создания, до (включительно)
 * @property \DateTime|null $createdAtLt Время создания, до (не включая)
 * @property \DateTime|null $expiresAtGte Время автоматического закрытия, от (включительно)
 * @property \DateTime|null $expiresAtGt Время автоматического закрытия, от (не включая)
 * @property \DateTime|null $expiresAtLte Время автоматического закрытия, до (включительно)
 * @property \DateTime|null $expiresAtLt Время автоматического закрытия, до (не включая)
 * @property string|null $fullTextSearch Фильтр по описанию сделки — параметру description
 * @property string|null $status Статус платежа
 */
class DealsRequest extends AbstractRequest implements DealsRequestInterface
{
    /** Максимальное количество объектов платежа в выборке */
    const MAX_LIMIT_VALUE = 100;
    /** Минимальное количество символов для поиска */
    const MIN_LENGTH_DESCRIPTION = 4;

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
     * @var \DateTime Время автоматического закрытия, от (включительно)
     */
    private $_expiresAtGte;

    /**
     * @var \DateTime Время автоматического закрытия, от (не включая)
     */
    private $_expiresAtGt;

    /**
     * @var \DateTime Время автоматического закрытия, до (включительно)
     */
    private $_expiresAtLte;

    /**
     * @var \DateTime Время автоматического закрытия, до (не включая)
     */
    private $_expiresAtLt;

    /**
     * @var string Статус сделки
     */
    private $_status;

    /**
     * @var string Строка поиска
     */
    private $_fullTextSearch;

    /**
     * @var string Ограничение количества объектов платежа
     */
    private $_limit;

    /**
     * @var string Страница выдачи результатов, которую необходимо отобразить
     */
    private $_cursor;

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
                    'Invalid limit value in DealsRequest', 0, 'DealsRequest.limit', $value
                );
            }
            $this->_limit = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid limit value type in DealsRequest', 0, 'DealsRequest.limit', $value
            );
        }
    }

    /**
     * Страница выдачи результатов, которую необходимо отобразить
     * @return string|null
     */
    public function getCursor()
    {
        return $this->_cursor;
    }

    /**
     * Проверяет, была ли установлена страница выдачи результатов, которую необходимо отобразить
     * @return bool True если была установлена, false если нет
     */
    public function hasCursor()
    {
        return $this->_cursor !== null;
    }

    /**
     * Устанавливает страницу выдачи результатов, которую необходимо отобразить
     * @param string $value Страница выдачи результатов или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setCursor($value)
    {
        if ($value === null || $value === '') {
            $this->_cursor = null;
        } elseif (TypeCast::canCastToString($value)) {
            $this->_cursor = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid status value type in DealsRequest', 0, 'DealsRequest.limit', $value
            );
        }
    }

    /**
     * Возвращает дату создания от которой будут возвращены платежи или null, если дата не была установлена
     * @return \DateTime|null Время создания, от (включительно)
     */
    public function getCreatedAtGte()
    {
        return $this->_createdAtGte;
    }

    /**
     * Проверяет, была ли установлена дата создания от которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtGte()
    {
        return $this->_createdAtGte !== null;
    }

    /**
     * Устанавливает дату создания от которой выбираются платежи
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
                    'Invalid createdAtGte value in DealsRequest', 0, 'PaymentRequest.createdAtGte'
                );
            }
            $this->_createdAtGte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid createdAtGte value type in DealsRequest', 0, 'PaymentRequest.createdAtGte'
            );
        }
    }

    /**
     * Возвращает дату создания от которой будут возвращены платежи или null, если дата не была установлена
     * @return \DateTime|null Время создания, от (не включая)
     */
    public function getCreatedAtGt()
    {
        return $this->_createdAtGt;
    }

    /**
     * Проверяет, была ли установлена дата создания от которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtGt()
    {
        return $this->_createdAtGt !== null;
    }

    /**
     * Устанавливает дату создания от которой выбираются платежи
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
                    'Invalid createdAtGt value in DealsRequest', 0, 'PaymentRequest.createdAtGt'
                );
            }
            $this->_createdAtGt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid createdAtGt value type in DealsRequest', 0, 'PaymentRequest.createdAtGt'
            );
        }
    }

    /**
     * Возвращает дату создания до которой будут возвращены платежи или null, если дата не была установлена
     * @return \DateTime|null Время создания, до (включительно)
     */
    public function getCreatedAtLte()
    {
        return $this->_createdAtLte;
    }

    /**
     * Проверяет, была ли установлена дата создания до которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtLte()
    {
        return $this->_createdAtLte !== null;
    }

    /**
     * Устанавливает дату создания до которой выбираются платежи
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
                    'Invalid createdAtLte value in DealsRequest', 0, 'PaymentRequest.createdAtLte'
                );
            }
            $this->_createdAtLte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid createdAtLte value type in DealsRequest', 0, 'PaymentRequest.createdAtLte'
            );
        }
    }

    /**
     * Возвращает дату создания до которой будут возвращены платежи или null, если дата не была установлена
     * @return \DateTime|null Время создания, до (не включая)
     */
    public function getCreatedAtLt()
    {
        return $this->_createdAtLt;
    }

    /**
     * Проверяет, была ли установлена дата создания до которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtLt()
    {
        return $this->_createdAtLt !== null;
    }

    /**
     * Устанавливает дату создания до которой выбираются платежи
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
                    'Invalid createdAtLt value in DealsRequest', 0, 'PaymentRequest.createdAtLt'
                );
            }
            $this->_createdAtLt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid createdAlLt value type in DealsRequest', 0, 'PaymentRequest.createdAtLt'
            );
        }
    }

    /**
     * Возвращает дату автоматического закрытия от которой будут возвращены платежи или null, если дата не была установлена
     * @return \DateTime|null Время автоматического закрытия, от (включительно)
     */
    public function getExpiresAtGte()
    {
        return $this->_expiresAtGte;
    }

    /**
     * Проверяет, была ли установлена дата автоматического закрытия от которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasExpiresAtGte()
    {
        return $this->_expiresAtGte !== null;
    }

    /**
     * Устанавливает дату автоматического закрытия от которой выбираются платежи
     * @param \DateTime|string|int|null $value Время автоматического закрытия, от (включительно) или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setExpiresAtGte($value)
    {
        if ($value === null || $value === '') {
            $this->_expiresAtGte = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid expiresAtGte value in DealsRequest', 0, 'PaymentRequest.expiresAtGte'
                );
            }
            $this->_expiresAtGte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid expiresAtGte value type in DealsRequest', 0, 'PaymentRequest.expiresAtGte'
            );
        }
    }

    /**
     * Возвращает дату автоматического закрытия от которой будут возвращены платежи или null, если дата не была установлена
     * @return \DateTime|null Время автоматического закрытия, от (не включая)
     */
    public function getExpiresAtGt()
    {
        return $this->_expiresAtGt;
    }

    /**
     * Проверяет, была ли установлена дата автоматического закрытия от которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasExpiresAtGt()
    {
        return $this->_expiresAtGt !== null;
    }

    /**
     * Устанавливает дату автоматического закрытия от которой выбираются платежи
     * @param \DateTime|string|int|null $value Время автоматического закрытия, от (не включая) или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setExpiresAtGt($value)
    {
        if ($value === null || $value === '') {
            $this->_expiresAtGt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid expiresAtGt value in DealsRequest', 0, 'PaymentRequest.expiresAtGt'
                );
            }
            $this->_expiresAtGt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid expiresAtGt value type in DealsRequest', 0, 'PaymentRequest.expiresAtGt'
            );
        }
    }

    /**
     * Возвращает дату автоматического закрытия до которой будут возвращены платежи или null, если дата не была установлена
     * @return \DateTime|null Время автоматического закрытия, до (включительно)
     */
    public function getExpiresAtLte()
    {
        return $this->_expiresAtLte;
    }

    /**
     * Проверяет, была ли установлена дата автоматического закрытия до которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasExpiresAtLte()
    {
        return $this->_expiresAtLte !== null;
    }

    /**
     * Устанавливает дату автоматического закрытия до которой выбираются платежи
     * @param \DateTime|string|int|null $value Время автоматического закрытия, до (включительно) или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setExpiresAtLte($value)
    {
        if ($value === null || $value === '') {
            $this->_expiresAtLte = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid expiresAtLte value in DealsRequest', 0, 'PaymentRequest.expiresAtLte'
                );
            }
            $this->_expiresAtLte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid expiresAtLte value type in DealsRequest', 0, 'PaymentRequest.expiresAtLte'
            );
        }
    }

    /**
     * Возвращает дату автоматического закрытия до которой будут возвращены платежи или null, если дата не была установлена
     * @return \DateTime|null Время автоматического закрытия, до (не включая)
     */
    public function getExpiresAtLt()
    {
        return $this->_expiresAtLt;
    }

    /**
     * Проверяет, была ли установлена дата автоматического закрытия до которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasExpiresAtLt()
    {
        return $this->_expiresAtLt !== null;
    }

    /**
     * Устанавливает дату автоматического закрытия до которой выбираются платежи
     * @param \DateTime|string|int|null $value Время автоматического закрытия, до (не включая) или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setExpiresAtLt($value)
    {
        if ($value === null || $value === '') {
            $this->_expiresAtLt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid expiresAtLt value in DealsRequest', 0, 'PaymentRequest.expiresAtLt'
                );
            }
            $this->_expiresAtLt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid expiresAlLt value type in DealsRequest', 0, 'PaymentRequest.expiresAtLt'
            );
        }
    }

    /**
     * Возвращает статус выбираемых сделок или null, если он до этого не был установлен
     * @return string|null Статус выбираемых сделок
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Проверяет, был ли установлен статус выбираемых сделок
     * @return bool True если статус был установлен, false если нет
     */
    public function hasStatus()
    {
        return $this->_status !== null;
    }

    /**
     * Устанавливает статус выбираемых сделок
     * @param string $value Статус выбираемых сделок или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение не является валидным статусом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setStatus($value)
    {
        if ($value === null || $value === '') {
            $this->_status = null;
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (!DealStatus::valueExists((string)$value)) {
                throw new InvalidPropertyValueException(
                    'Invalid status value in DealsRequest', 0, 'DealsRequest.status', $value
                );
            } else {
                $this->_status = (string)$value;
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid status value in DealsRequest', 0, 'DealsRequest.status', $value
            );
        }
    }

    /**
     * Возвращает фильтр по описанию выбираемых сделок или null, если он до этого не был установлен
     * @return string|null Фильтр по описанию выбираемых сделок
     */
    public function getFullTextSearch()
    {
        return $this->_fullTextSearch;
    }

    /**
     * Проверяет, был ли установлен фильтр по описанию выбираемых сделок
     * @return bool True если фильтр по описанию был установлен, false если нет
     */
    public function hasFullTextSearch()
    {
        return $this->_fullTextSearch !== null;
    }

    /**
     * Устанавливает фильтр по описанию выбираемых сделок
     * @param string $value Фильтр по описанию выбираемых сделок или null, чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение не является валидным
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setFullTextSearch($value)
    {
        if ($value === null || $value === '') {
            $this->_fullTextSearch = null;
        } elseif (TypeCast::canCastToEnumString($value)) {
            $length = mb_strlen((string)$value, 'utf-8');
            if ($length > SafeDeal::MAX_LENGTH_DESCRIPTION) {
                throw new InvalidPropertyValueException(
                    'The value of the full_text_search parameter is too long. Max length is ' . SafeDeal::MAX_LENGTH_DESCRIPTION,
                    0,
                    'DealsRequest.fullTextSearch',
                    $value
                );
            }
            if ($length < self::MIN_LENGTH_DESCRIPTION) {
                throw new InvalidPropertyValueException(
                    'The value of the full_text_search parameter is too short. Min length is ' . self::MIN_LENGTH_DESCRIPTION,
                    0,
                    'DealsRequest.fullTextSearch',
                    $value
                );
            }
            $this->_fullTextSearch = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid status value type in DealsRequest', 0, 'DealsRequest.fullTextSearch', $value
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
     * Возвращает инстанс билдера объектов запросов списка сделок магазина
     * @return DealsRequestBuilder Билдер объектов запросов списка сделок
     */
    public static function builder()
    {
        return new DealsRequestBuilder();
    }
}
