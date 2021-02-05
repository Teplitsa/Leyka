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

/**
 * Interface PaymentsRequestInterface
 *
 * @package YooKassa\Request\Payments
 *
 * @property-read string|null $cursor Страница выдачи результатов, которую необходимо отобразить
 * @property-read \DateTime|null $createdAtGte Время создания, от (включительно)
 * @property-read \DateTime|null $createdAtGt Время создания, от (не включая)
 * @property-read \DateTime|null $createdAtLte Время создания, до (включительно)
 * @property-read \DateTime|null $createdAtLt Время создания, до (не включая)
 * @property-read \DateTime|null $capturedAtGte Время подтверждения, от (включительно)
 * @property-read \DateTime|null $capturedAtGt Время подтверждения, от (не включая)
 * @property-read \DateTime|null $capturedAtLte Время подтверждения, до (включительно)
 * @property-read \DateTime|null $capturedAtLt Время подтверждения, до (не включая)
 * @property-read integer|null $limit Ограничение количества объектов платежа, отображаемых на одной странице выдачи
 * @property-read string|null $recipientGatewayId Идентификатор шлюза.
 * @property-read string|null $status Статус платежа
 */
interface PaymentsRequestInterface
{
    /**
     * Возвращает страницу выдачи результатов или null если она до этого не была установлена
     * @return string|null Страница выдачи результатов
     */
    function getCursor();

    /**
     * Проверяет была ли установлена страница выдачи результатов
     * @return bool True если страница выдачи результатов была установлена, false если нет
     */
    function hasCursor();

    /**
     * Устанавливает страницу выдачи результатов
     * @param string $value Страница
     * @return void
     */
    function setCursor($value);

    /**
     * Возвращает дату создания от которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время создания, от (включительно)
     */
    function getCreatedAtGte();

    /**
     * Проверяет была ли установлена дата создания от которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtGte();

    /**
     * Устанавливает дату создания от которой выбираются платежи
     * @param \DateTime $value Дата
     * @return void
     */
    function setCreatedAtGte($value);

    /**
     * Возвращает дату создания от которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время создания, от (не включая)
     */
    function getCreatedAtGt();

    /**
     * Проверяет была ли установлена дата создания от которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtGt();

    /**
     * Устанавливает дату создания от которой выбираются платежи
     * @param \DateTime $value Дата
     * @return void
     */
    function setCreatedAtGt($value);

    /**
     * Возвращает дату создания до которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время создания, до (включительно)
     */
    function getCreatedAtLte();

    /**
     * Проверяет была ли установлена дата создания до которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtLte();

    /**
     * Устанавливает дату создания до которой выбираются платежи
     * @param \DateTime $value Дата
     * @return void
     */
    function setCreatedAtLte($value);

    /**
     * Возвращает дату создания до которой будут возвращены платежи или null если дата не была установлена
     * @return \DateTime|null Время создания, до (не включая)
     */
    function getCreatedAtLt();

    /**
     * Проверяет была ли установлена дата создания до которой выбираются платежи
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtLt();

    /**
     * Устанавливает дату создания до которой выбираются платежи
     * @param \DateTime $value Дата
     * @return void
     */
    function setCreatedAtLt($value);

    /**
     * Возвращает ограничение количества объектов платежа или null если оно до этого не было установлено
     * @return string|null Ограничение количества объектов платежа
     */
    function getLimit();

    /**
     * Проверяет было ли установлено ограничение количества объектов платежа
     * @return bool True если ограничение количества объектов платежа было установлено, false если нет
     */
    function hasLimit();

    /**
     * Устанавливает ограничение количества объектов платежа
     * @param int $value Количества объектов платежа на странице
     * @return void
     */
    function setLimit($value);

    /**
     * Возвращает статус выбираемых платежей или null если он до этого не был установлен
     * @return string|null Статус выбираемых платежей
     */
    function getStatus();

    /**
     * Проверяет был ли установлен статус выбираемых платежей
     * @return bool True если статус был установлен, false если нет
     */
    function hasStatus();

    /**
     * Устанавливает статус выбираемых платежей
     * @param string $value Статус платежей
     * @return void
     */
    function setStatus($value);
}
