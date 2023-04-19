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

namespace YooKassa\Request\Refunds;

/**
 * Интерфейс объекта запроса списка возвратов
 *
 * @package YooKassa
 *
 * @property-read string $paymentId Идентификатор платежа
 * @property-read \DateTime $createdAtGte Время создания, от (включительно)
 * @property-read \DateTime $createdAtGt Время создания, от (не включая)
 * @property-read \DateTime $createdAtLte Время создания, до (включительно)
 * @property-read \DateTime $createdAtLt Время создания, до (не включая)
 * @property-read string $status Статус возврата
 * @property-read string $cursor Токен для получения следующей страницы выборки
 * @property-read integer|null $limit Ограничение количества объектов, отображаемых на одной странице выдачи
 */
interface RefundsRequestInterface
{
    /**
     * Возвращает идентификатор платежа если он задан или null
     * @return string|null Идентификатор платежа
     */
    function getPaymentId();

    /**
     * Проверяет, был ли задан идентификатор платежа
     * @return bool True если идентификатор был задан, false если нет
     */
    function hasPaymentId();

    /**
     * Возвращает дату создания от которой будут возвращены возвраты или null, если дата не была установлена
     * @return \DateTime|null Время создания, от (включительно)
     */
    function getCreatedAtGte();

    /**
     * Проверяет, была ли установлена дата создания от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtGte();

    /**
     * Возвращает дату создания от которой будут возвращены возвраты или null, если дата не была установлена
     * @return \DateTime|null Время создания, от (не включая)
     */
    function getCreatedAtGt();

    /**
     * Проверяет, была ли установлена дата создания от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtGt();

    /**
     * Возвращает дату создания до которой будут возвращены возвраты или null, если дата не была установлена
     * @return \DateTime|null Время создания, до (включительно)
     */
    function getCreatedAtLte();

    /**
     * Проверяет, была ли установлена дата создания до которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtLte();

    /**
     * Возвращает дату создания до которой будут возвращены возвраты или null, если дата не была установлена
     * @return \DateTime|null Время создания, до (не включая)
     */
    function getCreatedAtLt();

    /**
     * Проверяет, была ли установлена дата создания до которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtLt();

    /**
     * Возвращает статус выбираемых возвратов или null, если он до этого не был установлен
     * @return string|null Статус выбираемых возвратов
     */
    function getStatus();

    /**
     * Проверяет, был ли установлен статус выбираемых возвратов
     * @return bool True если статус был установлен, false если нет
     */
    function hasStatus();

    /**
     * Возвращает токен для получения следующей страницы выборки
     * @return string|null Токен для получения следующей страницы выборки
     */
    function getCursor();

    /**
     * Проверяет, был ли установлен токен следующей страницы
     * @return bool True если токен был установлен, false если нет
     */
    function hasCursor();
}
