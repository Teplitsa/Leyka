<?php

/**
 * The MIT License
 *
 * Copyright (c) 2020 "YooMoney", NBСO LLC
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

use Exception;
use YooKassa\Common\AbstractRequest;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\PaymentMethodType;
use YooKassa\Model\PaymentStatus;

/**
 * Класс объекта запроса к API для получения списка платежей магазина
 *
 * @property \DateTime|null $createdAtGte Время создания, от (включительно)
 * @property \DateTime|null $createdAtGt Время создания, от (не включая)
 * @property \DateTime|null $createdAtLte Время создания, до (включительно)
 * @property \DateTime|null $createdAtLt Время создания, до (не включая)
 * @property \DateTime|null $capturedAtGte Время подтверждения, от (включительно)
 * @property \DateTime|null $capturedAtGt Время подтверждения, от (не включая)
 * @property \DateTime|null $capturedAtLte Время подтверждения, до (включительно)
 * @property \DateTime|null $capturedAtLt Время подтверждения, до (не включая)
 * @property string|null $status Статус платежа
 * @property string|null $paymentMethod Платежный метод
 * @property integer|null $limit Ограничение количества объектов платежа, отображаемых на одной странице выдачи
 * @property string|null $cursor Страница выдачи результатов, которую необходимо отобразить
 */
class PaymentsRequest extends AbstractRequest implements PaymentsRequestInterface
{
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
     * @var \DateTime Время подтверждения, от (включительно)
     */
    private $_capturedAtGte;

    /**
     * @var \DateTime Время подтверждения, от (не включая)
     */
    private $_capturedAtGt;

    /**
     * @var \DateTime Время подтверждения, до (включительно)
     */
    private $_capturedAtLte;

    /**
     * @var \DateTime Время подтверждения, до (не включая)
     */
    private $_capturedAtLt;

    /**
     * @var string Статус платежа
     */
    private $_status;

    /**
     * @var string Платежный метод
     */
    private $_paymentMethod;

    /**
     * @var string Ограничение количества объектов платежа
     */
    private $_limit;

    /**
     * @var string Страница выдачи результатов, которую необходимо отобразить
     */
    private $_cursor;

    /**
     * Возвращает дату создания от которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время создания, от (включительно)
     */
    public function getCreatedAtGte()
    {
        return $this->_createdAtGte;
    }

    /**
     * Проверяет была ли установлена дата создания от которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtGte()
    {
        return $this->_createdAtGte !== null;
    }

    /**
     * Устанавливает дату создания от которой выбираются платежи
     * @param \DateTime|string|int|null $value Время создания, от (включительно) или null чтобы удалить значение
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
                    'Invalid createdAtGte value in PaymentsRequest', 0, 'PaymentRequest.createdAtGte'
                );
            }
            $this->_createdAtGte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid createdAtGte value type in PaymentsRequest', 0, 'PaymentRequest.createdAtGte'
            );
        }
    }

    /**
     * Возвращает дату создания от которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время создания, от (не включая)
     */
    public function getCreatedAtGt()
    {
        return $this->_createdAtGt;
    }

    /**
     * Проверяет была ли установлена дата создания от которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtGt()
    {
        return $this->_createdAtGt !== null;
    }

    /**
     * Устанавливает дату создания от которой выбираются платежи
     * @param \DateTime|string|int|null $value Время создания, от (не включая) или null чтобы удалить значение
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
                    'Invalid createdAtGt value in PaymentsRequest', 0, 'PaymentRequest.createdAtGt'
                );
            }
            $this->_createdAtGt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid createdAtGt value type in PaymentsRequest', 0, 'PaymentRequest.createdAtGt'
            );
        }
    }

    /**
     * Возвращает дату создания до которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время создания, до (включительно)
     */
    public function getCreatedAtLte()
    {
        return $this->_createdAtLte;
    }

    /**
     * Проверяет была ли установлена дата создания до которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtLte()
    {
        return $this->_createdAtLte !== null;
    }

    /**
     * Устанавливает дату создания до которой выбираются платежи
     * @param \DateTime|string|int|null $value Время создания, до (включительно) или null чтобы удалить значение
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
                    'Invalid createdAtLte value in PaymentsRequest', 0, 'PaymentRequest.createdAtLte'
                );
            }
            $this->_createdAtLte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid createdAtLte value type in PaymentsRequest', 0, 'PaymentRequest.createdAtLte'
            );
        }
    }

    /**
     * Возвращает дату создания до которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время создания, до (не включая)
     */
    public function getCreatedAtLt()
    {
        return $this->_createdAtLt;
    }

    /**
     * Проверяет была ли установлена дата создания до которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedAtLt()
    {
        return $this->_createdAtLt !== null;
    }

    /**
     * Устанавливает дату создания до которой выбираются платежи
     * @param \DateTime|string|int|null $value Время создания, до (не включая) или null чтобы удалить значение
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
                    'Invalid createdAtLt value in PaymentsRequest', 0, 'PaymentRequest.createdAtLt'
                );
            }
            $this->_createdAtLt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid createdAlLt value type in PaymentsRequest', 0, 'PaymentRequest.createdAtLt'
            );
        }
    }

    /**
     * Возвращает дату подтверждения от которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время подтверждения, от (включительно)
     */
    public function getCapturedAtGte()
    {
        return $this->_capturedAtGte;
    }

    /**
     * Проверяет была ли установлена дата подтверждения от которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCapturedAtGte()
    {
        return $this->_capturedAtGte !== null;
    }

    /**
     * Устанавливает дату подтверждения от которой выбираются платежи
     * @param \DateTime|string|int|null $value Время подтверждения, от (включительно) или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCapturedAtGte($value)
    {
        if ($value === null || $value === '') {
            $this->_capturedAtGte = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid capturedAtGte value in PaymentsRequest', 0, 'PaymentRequest.capturedAtGte'
                );
            }
            $this->_capturedAtGte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid capturedAtGte value type in PaymentsRequest', 0, 'PaymentRequest.capturedAtGte'
            );
        }
    }

    /**
     * Возвращает дату подтверждения от которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время подтверждения, от (не включая)
     */
    public function getCapturedAtGt()
    {
        return $this->_capturedAtGt;
    }

    /**
     * Проверяет была ли установлена дата подтверждения от которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCapturedAtGt()
    {
        return $this->_capturedAtGt !== null;
    }

    /**
     * Устанавливает дату подтверждения от которой выбираются платежи
     * @param \DateTime|string|int|null $value Время подтверждения, от (не включая) или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCapturedAtGt($value)
    {
        if ($value === null || $value === '') {
            $this->_capturedAtGt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid capturedAtGt value in PaymentsRequest', 0, 'PaymentRequest.capturedAtGt'
                );
            }
            $this->_capturedAtGt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid capturedAtGt value type in PaymentsRequest', 0, 'PaymentRequest.capturedAtGt'
            );
        }
    }

    /**
     * Возвращает дату подтверждения до которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время подтверждения, до (включительно)
     */
    public function getCapturedAtLte()
    {
        return $this->_capturedAtLte;
    }

    /**
     * Проверяет была ли установлена дата подтверждения до которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCapturedAtLte()
    {
        return $this->_capturedAtLte !== null;
    }

    /**
     * Устанавливает дату подтверждения до которой выбираются платежи
     * @param \DateTime|string|int|null $value Время подтверждения, до (включительно) или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCapturedAtLte($value)
    {
        if ($value === null || $value === '') {
            $this->_capturedAtLte = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid capturedAtLte value in PaymentsRequest', 0, 'PaymentRequest.capturedAtLte'
                );
            }
            $this->_capturedAtLte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid capturedAtLte value type in PaymentsRequest', 0, 'PaymentRequest.capturedAtLte'
            );
        }
    }

    /**
     * Возвращает дату подтверждения до которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время подтверждения, до (не включая)
     */
    public function getCapturedAtLt()
    {
        return $this->_capturedAtLt;
    }

    /**
     * Проверяет была ли установлена дата подтверждения до которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCapturedAtLt()
    {
        return $this->_capturedAtLt !== null;
    }

    /**
     * Устанавливает дату подтверждения до которой выбираются платежи
     * @param \DateTime|string|int|null $value Время подтверждения, до (не включая) или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException|Exception Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCapturedAtLt($value)
    {
        if ($value === null || $value === '') {
            $this->_capturedAtLt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid capturedAtLt value in PaymentsRequest', 0, 'PaymentRequest.capturedAtLt'
                );
            }
            $this->_capturedAtLt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid capturedAlLt value type in PaymentsRequest', 0, 'PaymentRequest.capturedAtLt'
            );
        }
    }

    /**
     * Возвращает статус выбираемых платежей или null если он до этого не был установлен
     * @return string|null Статус выбираемых платежей
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Проверяет был ли установлен статус выбираемых платежей
     * @return bool True если статус был установлен, false если нет
     */
    public function hasStatus()
    {
        return $this->_status !== null;
    }

    /**
     * Устанавливает статус выбираемых платежей
     * @param string $value Статус выбираемых платежей или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение не является валидным статусом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setStatus($value)
    {
        if ($value === null || $value === '') {
            $this->_status = null;
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (!PaymentStatus::valueExists((string)$value)) {
                throw new InvalidPropertyValueException(
                    'Invalid status value in PaymentsRequest', 0, 'PaymentsRequest.status', $value
                );
            } else {
                $this->_status = (string)$value;
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid status value in PaymentsRequest', 0, 'PaymentsRequest.status', $value
            );
        }
    }

    /**
     * Возвращает платежный метод выбираемых платежей или null если он до этого не был установлен
     * @return string|null Платежный метод выбираемых платежей
     */
    public function getPaymentMethod()
    {
        return $this->_paymentMethod;
    }

    /**
     * Проверяет был ли установлен платежный метод выбираемых платежей
     * @return bool True если платежный метод был установлен, false если нет
     */
    public function hasPaymentMethod()
    {
        return $this->_paymentMethod !== null;
    }

    /**
     * Устанавливает платежный метод выбираемых платежей
     * @param string $value Платежный метод выбираемых платежей или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Выбрасывается если переданное значение не является валидным статусом
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setPaymentMethod($value)
    {
        if ($value === null || $value === '') {
            $this->_paymentMethod = null;
        } elseif (TypeCast::canCastToEnumString($value)) {
            if (!PaymentMethodType::valueExists((string)$value)) {
                throw new InvalidPropertyValueException(
                    'Invalid status value in PaymentsRequest', 0, 'PaymentsRequest.paymentMethod', $value
                );
            } else {
                $this->_paymentMethod = (string)$value;
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid status value type in PaymentsRequest', 0, 'PaymentsRequest.paymentMethod', $value
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
     * Проверяет был ли установлено ограничение количества объектов платежа
     * @return bool True если было установлено, false если нет
     */
    public function hasLimit()
    {
        return $this->_limit !== null;
    }

    /**
     * Устанавливает ограничение количества объектов платежа
     * @param integer|null $value Ограничение количества объектов платежа или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передано не целое число
     */
    public function setLimit($value)
    {
        if ($value === null || $value === '') {
            $this->_limit = null;
        } elseif (is_int($value)) {
            if ($value < 0 || $value > self::MAX_LIMIT_VALUE) {
                throw new InvalidPropertyValueException(
                    'Invalid limit value in PaymentsRequest', 0, 'PaymentsRequest.limit', $value
                );
            }
            $this->_limit = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid limit value type in PaymentsRequest', 0, 'PaymentsRequest.limit', $value
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
     * Проверяет был ли установлена страница выдачи результатов, которую необходимо отобразить
     * @return bool True если была установлена, false если нет
     */
    public function hasCursor()
    {
        return $this->_cursor !== null;
    }

    /**
     * Устанавливает cтраницw выдачи результатов, которую необходимо отобразить
     * @param string $value Страница выдачи результатов или null чтобы удалить значение
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
                'Invalid status value type in PaymentsRequest', 0, 'PaymentsRequest.limit', $value
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
     * Возвращает инстанс билдера объектов запросов списка платежей магазина
     * @return PaymentsRequestBuilder Билдер объектов запросов списка платежей
     */
    public static function builder()
    {
        return new PaymentsRequestBuilder();
    }
}
