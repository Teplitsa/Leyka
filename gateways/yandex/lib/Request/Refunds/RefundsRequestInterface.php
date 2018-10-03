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

/**
 * Интерфейс объекта запроса списка возвратов
 *
 * @package YandexCheckout\Request\Refunds
 *
 * @property-read string $refundId
 * @property-read string $paymentId Идентификатор платежа
 * @property-read string $accountId Идентификатор магазина
 * @property-read string $gatewayId Идентификатор шлюза
 * @property-read \DateTime $createdGte Время создания, от (включительно)
 * @property-read \DateTime $createdGt Время создания, от (не включая)
 * @property-read \DateTime $createdLte Время создания, до (включительно)
 * @property-read \DateTime $createdLt Время создания, до (не включая)
 * @property-read \DateTime $authorizedGte Время проведения операции, от (включительно)
 * @property-read \DateTime $authorizedGt Время проведения операции, от (не включая)
 * @property-read \DateTime $authorizedLte Время проведения, до (включительно)
 * @property-read \DateTime $authorizedLt Время проведения, до (не включая)
 * @property-read string $status Статус возврата
 * @property-read string $nextPage Токен для получения следующей страницы выборки
 */
interface RefundsRequestInterface
{
    /**
     * Возвращает идентификатор возврата
     * @return string Идентификатор возврата
     */
    function getRefundId();

    /**
     * Проверяет был ли установлен идентификатор возврата
     * @return bool True если идентификатор возврата был установлен, false если не был
     */
    function hasRefundId();

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
     * Возвращает идентификатор магазина, если он был задан
     * @return string|null Идентификатор магазина
     */
    function getAccountId();

    /**
     * Проверяет, был ли установлен идентификатор магазина
     * @return bool True если идентификатор магазина был установлен, false если нет
     */
    function hasAccountId();

    /**
     * Возвращает идентификатор шлюза
     * @return string|null Идентификатор шлюза
     */
    function getGatewayId();

    /**
     * Проверяет был ли установлен идентификатор шлюза
     * @return bool True если идентификатор шлюза был установлен, false если нет
     */
    function hasGatewayId();

    /**
     * Возвращает дату создания от которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время создания, от (включительно)
     */
    function getCreatedGte();

    /**
     * Проверяет была ли установлена дата создания от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedGte();

    /**
     * Возвращает дату создания от которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время создания, от (не включая)
     */
    function getCreatedGt();

    /**
     * Проверяет была ли установлена дата создания от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedGt();

    /**
     * Возвращает дату создания до которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время создания, до (включительно)
     */
    function getCreatedLte();

    /**
     * Проверяет была ли установлена дата создания до которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedLte();

    /**
     * Возвращает дату создания до которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время создания, до (не включая)
     */
    function getCreatedLt();

    /**
     * Проверяет была ли установлена дата создания до которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedLt();

    /**
     * Возвращает дату проведения от которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время проведения операции, от (включительно)
     */
    function getAuthorizedGte();

    /**
     * Проверяет была ли установлена дата проведения от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasAuthorizedGte();

    /**
     * Возвращает дату проведения от которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время проведения операции, от (не включая)
     */
    function getAuthorizedGt();

    /**
     * Проверяет была ли установлена дата проведения от которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasAuthorizedGt();

    /**
     * Возвращает дату проведения до которой будут возвращены возвраты или null если дата не была установлена
     * @return \DateTime|null Время проведения, до (включительно)
     */
    function getAuthorizedLte();

    /**
     * Проверяет была ли установлена дата проведения до которой выбираются возвраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasAuthorizedLte();

    /**
     * Возвращает дату проведения до которой будут возвращены платежи возвраты или null если она не была установлена
     * @return \DateTime|null Время проведения, до (не включая)
     */
    function getAuthorizedLt();

    /**
     * Проверяет была ли установлена дата проведения до которой выбираются вовзраты
     * @return bool True если дата была установлена, false если нет
     */
    function hasAuthorizedLt();

    /**
     * Возвращает статус выбираемых возвратов или null если он до этого не был установлен
     * @return string|null Статус выбираемых возвратов
     */
    function getStatus();

    /**
     * Проверяет был ли установлен статус выбираемых возвратов
     * @return bool True если статус был установлен, false если нет
     */
    function hasStatus();

    /**
     * Возвращает токен для получения следующей страницы выборки
     * @return string|null Токен для получения следующей страницы выборки
     */
    function getNextPage();

    /**
     * Проверяет был ли установлен токен следующей страницы
     * @return bool True если токен был установлен, false если нет
     */
    function hasNextPage();
}
