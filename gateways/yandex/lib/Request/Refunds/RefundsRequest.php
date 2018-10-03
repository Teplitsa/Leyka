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

use YandexCheckout\Common\AbstractRequest;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueException;
use YandexCheckout\Common\Exceptions\InvalidPropertyValueTypeException;
use YandexCheckout\Helpers\TypeCast;
use YandexCheckout\Model\RefundStatus;

/**
 * Класс объекта запроса к API списка возвратов магазина
 *
 * @package YandexCheckout\Request\Refunds
 *
 * @property string $refundId Идентификатор возврата
 * @property string $paymentId Идентификатор платежа
 * @property string $accountId Идентификатор магазина
 * @property string $gatewayId Идентификатор шлюза
 * @property \DateTime $createdGte Время создания, от (включительно)
 * @property \DateTime $createdGt Время создания, от (не включая)
 * @property \DateTime $createdLte Время создания, до (включительно)
 * @property \DateTime $createdLt Время создания, до (не включая)
 * @property \DateTime $authorizedGte Время проведения операции, от (включительно)
 * @property \DateTime $authorizedGt Время проведения операции, от (не включая)
 * @property \DateTime $authorizedLte Время проведения, до (включительно)
 * @property \DateTime $authorizedLt Время проведения, до (не включая)
 * @property string $status Статус возврата
 * @property string $nextPage Токен для получения следующей страницы выборки
 */
class RefundsRequest extends AbstractRequest implements RefundsRequestInterface
{
    /**
     * @var string Идентификатор возврата
     */
    private $_refundId;

    /**
     * @var string Идентификатор шлюза
     */
    private $_paymentId;

    /**
     * @var string Идентификатор магазина
     */
    private $_accountId;

    /**
     * @var string Идентификатор шлюза
     */
    private $_gatewayId;

    /**
     * @var \DateTime Время создания, от (включительно)
     */
    private $_createdGte;

    /**
     * @var \DateTime Время создания, от (не включая)
     */
    private $_createdGt;

    /**
     * @var \DateTime Время создания, до (включительно)
     */
    private $_createdLte;

    /**
     * @var \DateTime Время создания, до (не включая)
     */
    private $_createdLt;

    /**
     * @var \DateTime Время проведения операции, от (включительно)
     */
    private $_authorizedGte;

    /**
     * @var \DateTime Время проведения операции, от (не включая)
     */
    private $_authorizedGt;

    /**
     * @var \DateTime Время проведения, до (включительно)
     */
    private $_authorizedLte;

    /**
     * @var \DateTime Время проведения, до (не включая)
     */
    private $_authorizedLt;

    /**
     * @var string Статус возврата
     */
    private $_status;

    /**
     * @var string Токен для получения следующей страницы выборки
     */
    private $_nextPage;

    /**
     * Возвращает идентификатор возврата
     * @return string Идентификатор возврата
     */
    public function getRefundId()
    {
        return $this->_refundId;
    }

    /**
     * Проверяет был ли установлен идентификатор возврата
     * @return bool True если идентификатор возврата был установлен, false если не был
     */
    public function hasRefundId()
    {
        return $this->_refundId !== null;
    }

    /**
     * Устанавливает идентификатор возврата
     * @param string $value Идентификатор возврата, который ищется в API
     *
     * @throws InvalidPropertyValueException Выбрасывается если длина переданного значения не равна 36
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setRefundId($value)
    {
        if ($value === null || $value === '') {
            $this->_refundId = null;
        } elseif (TypeCast::canCastToString($value)) {
            $length = mb_strlen((string)$value, 'utf-8');
            if ($length != 36) {
                throw new InvalidPropertyValueException(
                    'Invalid refund id value', 0, 'RefundsRequest.refundId', $value
                );
            }
            $this->_refundId = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid refund id value type', 0, 'RefundsRequest.refundId', $value
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
     * Устанавливает идентификатор платежа или null если требуется его удалить
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
                    'Invalid payment id value in RefundsRequest', 0, 'RefundsRequest.paymentId', $value
                );
            }
            $this->_paymentId = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid payment id value type in RefundsRequest', 0, 'RefundsRequest.paymentId', $value
            );
        }
    }

    /**
     * Возвращает идентификатор магазина, если он был задан
     * @return string|null Идентификатор магазина
     */
    public function getAccountId()
    {
        return $this->_accountId;
    }

    /**
     * Проверяет, был ли установлен идентификатор магазина
     * @return bool True если идентификатор магазина был установлен, false если нет
     */
    public function hasAccountId()
    {
        return !empty($this->_accountId);
    }

    /**
     * Устанавлвиает идентификатор магазина
     * @param string $value Идентификатор магазина или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setAccountId($value)
    {
        if ($value === null || $value === '') {
            $this->_accountId = null;
        } elseif (TypeCast::canCastToString($value)) {
            $this->_accountId = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid accountId value type in RefundsRequest', 0, 'RefundsRequest.accountId', $value
            );
        }
    }

    /**
     * Возвращает идентификатор шлюза
     * @return string|null Идентификатор шлюза
     */
    public function getGatewayId()
    {
        return $this->_gatewayId;
    }

    /**
     * Проверяет был ли установлен идентификатор шлюза
     * @return bool True если идентификатор шлюза был установлен, false если нет
     */
    public function hasGatewayId()
    {
        return !empty($this->_gatewayId);
    }

    /**
     * Устанавливает идентификатор шлюза
     * @param string|null $value Идентификатор шлюза или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setGatewayId($value)
    {
        if ($value === null || $value === '') {
            $this->_gatewayId = null;
        } elseif (TypeCast::canCastToString($value)) {
            $this->_gatewayId = (string)$value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid gatewayId value type in RefundsRequest', 0, 'RefundsRequest.gatewayId', $value
            );
        }
    }

    /**
     * Возвращает дату создания от которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время создания, от (включительно)
     */
    public function getCreatedGte()
    {
        return $this->_createdGte;
    }

    /**
     * Проверяет была ли установлена дата создания от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedGte()
    {
        return !empty($this->_createdGte);
    }

    /**
     * Устанавливает дату создания от которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время создания, от (включительно) или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedGte($value)
    {
        if ($value === null || $value === '') {
            $this->_createdGte = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid created_gte value in RefundsRequest', 0, 'RefundsRequest.createdGte'
                );
            }
            $this->_createdGte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid created_gte value type in RefundsRequest', 0, 'RefundsRequest.createdGte'
            );
        }
    }

    /**
     * Возвращает дату создания от которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время создания, от (не включая)
     */
    public function getCreatedGt()
    {
        return $this->_createdGt;
    }

    /**
     * Проверяет была ли установлена дата создания от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedGt()
    {
        return !empty($this->_createdGt);
    }

    /**
     * Устанавливает дату создания от которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время создания, от (не включая) или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedGt($value)
    {
        if ($value === null || $value === '') {
            $this->_createdGt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid created_gt value in RefundsRequest', 0, 'RefundsRequest.createdGt'
                );
            }
            $this->_createdGt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid created_gt value type in RefundsRequest', 0, 'RefundsRequest.createdGt'
            );
        }
    }

    /**
     * Возвращает дату создания до которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время создания, до (включительно)
     */
    public function getCreatedLte()
    {
        return $this->_createdLte;
    }

    /**
     * Проверяет была ли установлена дата создания до которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedLte()
    {
        return !empty($this->_createdLte);
    }

    /**
     * Устанавливает дату создания до которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время создания, до (включительно) или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedLte($value)
    {
        if ($value === null || $value === '') {
            $this->_createdLte = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid created_lte value in RefundsRequest', 0, 'RefundsRequest.createdLte'
                );
            }
            $this->_createdLte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid created_lte value type in RefundsRequest', 0, 'RefundsRequest.createdLte'
            );
        }
    }

    /**
     * Возвращает дату создания до которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время создания, до (не включая)
     */
    public function getCreatedLt()
    {
        return $this->_createdLt;
    }

    /**
     * Проверяет была ли установлена дата создания до которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasCreatedLt()
    {
        return !empty($this->_createdLt);
    }

    /**
     * Устанавливает дату создания до которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время создания, до (не включая) или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setCreatedLt($value)
    {
        if ($value === null || $value === '') {
            $this->_createdLt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid created_lt value in RefundsRequest', 0, 'RefundsRequest.createdLt'
                );
            }
            $this->_createdLt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid created_lt value type in RefundsRequest', 0, 'RefundsRequest.createdLt'
            );
        }
    }

    /**
     * Возвращает дату проведения от которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время проведения операции, от (включительно)
     */
    public function getAuthorizedGte()
    {
        return $this->_authorizedGte;
    }

    /**
     * Проверяет была ли установлена дата проведения от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasAuthorizedGte()
    {
        return !empty($this->_authorizedGte);
    }

    /**
     * Устанавливает дату проведения от которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время проведения операции, от (не включая) или null чтобы удалить
     * значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setAuthorizedGte($value)
    {
        if ($value === null || $value === '') {
            $this->_authorizedGte = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid authorized_gte value in RefundsRequest', 0, 'RefundsRequest.authorizedGte'
                );
            }
            $this->_authorizedGte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid authorized_gte value type in RefundsRequest', 0, 'RefundsRequest.authorizedGte'
            );
        }
    }

    /**
     * Возвращает дату проведения от которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время проведения операции, от (не включая)
     */
    public function getAuthorizedGt()
    {
        return $this->_authorizedGt;
    }

    /**
     * Проверяет была ли установлена дата проведения от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasAuthorizedGt()
    {
        return !empty($this->_authorizedGt);
    }

    /**
     * Устанавливает дату проведения от которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время проведения операции, от (не включая) или null чтобы удалить
     * значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setAuthorizedGt($value)
    {
        if ($value === null || $value === '') {
            $this->_authorizedGt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid authorized_gt value in RefundsRequest', 0, 'RefundsRequest.authorizedGt'
                );
            }
            $this->_authorizedGt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid authorized_gt value type in RefundsRequest', 0, 'RefundsRequest.authorizedGt'
            );
        }
    }

    /**
     * Возвращает дату проведения до которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время проведения, до (включительно)
     */
    public function getAuthorizedLte()
    {
        return $this->_authorizedLte;
    }

    /**
     * Проверяет была ли установлена дата проведения до которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasAuthorizedLte()
    {
        return !empty($this->_authorizedLte);
    }

    /**
     * Устанавливает дату проведения до которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время проведения, до (включительно) или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setAuthorizedLte($value)
    {
        if ($value === null || $value === '') {
            $this->_authorizedLte = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid authorized_lte value in RefundsRequest', 0, 'RefundsRequest.authorizedLte'
                );
            }
            $this->_authorizedLte = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid authorized_lte value type in RefundsRequest', 0, 'RefundsRequest.authorizedLte'
            );
        }
    }

    /**
     * Возвращает дату проведения до которой будут возвращены платежи возвраты или null если она не была установлена
     * @return \DateTime|null Время проведения, до (не включая)
     */
    public function getAuthorizedLt()
    {
        return $this->_authorizedLt;
    }

    /**
     * Проверяет была ли установлена дата проведения до которой выбираются вовзраты
     * @return bool True если дата была установлена, false если нет
     */
    public function hasAuthorizedLt()
    {
        return !empty($this->_authorizedLt);
    }

    /**
     * Устанавливает дату проведения до которой выбираются возвраты
     * @param \DateTime|string|int|null $value Время проведения, до (не включая) или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueException Генерируется если была передана дата в невалидном формате (была передана
     * строка или число, которые не удалось преобразовать в валидную дату)
     * @throws InvalidPropertyValueTypeException Генерируется если была передана дата с не тем типом (передана не
     * строка, не число и не значение типа \DateTime)
     */
    public function setAuthorizedLt($value)
    {
        if ($value === null || $value === '') {
            $this->_authorizedLt = null;
        } elseif (TypeCast::canCastToDateTime($value)) {
            $dateTime = TypeCast::castToDateTime($value);
            if ($dateTime === null) {
                throw new InvalidPropertyValueException(
                    'Invalid authorized_lt value in RefundsRequest', 0, 'RefundsRequest.authorizedLt'
                );
            }
            $this->_authorizedLt = $dateTime;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid authorized_lt value type in RefundsRequest', 0, 'RefundsRequest.authorizedLt'
            );
        }
    }

    /**
     * Возвращает статус выбираемых возвратов или null если он до этого не был установлен
     * @return string|null Статус выбираемых возвратов
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Проверяет был ли установлен статус выбираемых возвратов
     * @return bool True если статус был установлен, false если нет
     */
    public function hasStatus()
    {
        return !empty($this->_status);
    }

    /**
     * Устанавливает статус выбираемых возвратов
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
            if (!RefundStatus::valueExists((string)$value)) {
                throw new InvalidPropertyValueException(
                    'Invalid status value in RefundsRequest', 0, 'RefundsRequest.status', $value
                );
            } else {
                $this->_status = (string)$value;
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid status value in RefundsRequest', 0, 'RefundsRequest.status', $value
            );
        }
    }

    /**
     * Возвращает токен для получения следующей страницы выборки
     * @return string|null Токен для получения следующей страницы выборки
     */
    public function getNextPage()
    {
        return $this->_nextPage;
    }

    /**
     * Проверяет был ли установлен токен следующей страницы
     * @return bool True если токен был установлен, false если нет
     */
    public function hasNextPage()
    {
        return !empty($this->_nextPage);
    }

    /**
     * Устанавливает токен следующей страницы выборки
     * @param string $value Токен следующей страницы выборки или null чтобы удалить значение
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если в метод была передана не строка
     */
    public function setNextPage($value)
    {
        if ($value === null || $value === '') {
            $this->_nextPage = null;
        } elseif (TypeCast::canCastToString($value)) {
            $this->_nextPage = (string) $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid status value in PaymentsRequest', 0, 'PaymentsRequest.status', $value
            );
        }
    }

    /**
     * Проверяет валидность текущего объекта запроса
     * @return bool True если объект валиден, false если нет
     */
    public function validate()
    {
        if (empty($this->_accountId)) {
            $this->setValidationError('Shop id not specified');
            return false;
        }
        return true;
    }

    /**
     * Возвращает инстанс билдера объектов запросов списка возвратов магазина
     * @return RefundsRequestBuilder Билдер объектов запросов списка возвратов
     */
    public static function builder()
    {
        return new RefundsRequestBuilder();
    }
}
