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

/**
 * Interface DealsRequestInterface
 *
 * @package YooKassa
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
 * @property string|null $full_text_search Фильтр по описанию сделки — параметру description
 * @property string|null $status Статус платежа
 */
interface DealsRequestInterface
{
    /**
     * Возвращает страницу выдачи результатов или null, если она до этого не была установлена
     * @return string|null Страница выдачи результатов
     */
    function getCursor();

    /**
     * Проверяет, была ли установлена страница выдачи результатов
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
     * Возвращает дату создания от которой будут возвращены сделки или null, если дата не была установлена
     * @return \DateTime|null Время создания, от (включительно)
     */
    function getCreatedAtGte();

    /**
     * Проверяет, была ли установлена дата создания от которой выбираются сделки
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtGte();

    /**
     * Устанавливает дату создания от которой выбираются сделки
     * @param \DateTime $value Дата
     * @return void
     */
    function setCreatedAtGte($value);

    /**
     * Возвращает дату создания от которой будут возвращены сделки или null, если дата не была установлена
     * @return \DateTime|null Время создания, от (не включая)
     */
    function getCreatedAtGt();

    /**
     * Проверяет, была ли установлена дата создания от которой выбираются сделки
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtGt();

    /**
     * Устанавливает дату создания от которой выбираются сделки
     * @param \DateTime $value Дата
     * @return void
     */
    function setCreatedAtGt($value);

    /**
     * Возвращает дату создания до которой будут возвращены сделки или null, если дата не была установлена
     * @return \DateTime|null Время создания, до (включительно)
     */
    function getCreatedAtLte();

    /**
     * Проверяет, была ли установлена дата создания до которой выбираются сделки
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtLte();

    /**
     * Устанавливает дату создания до которой выбираются сделки
     * @param \DateTime $value Дата
     * @return void
     */
    function setCreatedAtLte($value);

    /**
     * Возвращает дату создания до которой будут возвращены сделки или null, если дата не была установлена
     * @return \DateTime|null Время создания, до (не включая)
     */
    function getCreatedAtLt();

    /**
     * Проверяет, была ли установлена дата создания до которой выбираются сделки
     * @return bool True если дата была установлена, false если нет
     */
    function hasCreatedAtLt();

    /**
     * Устанавливает дату создания до которой выбираются сделки
     * @param \DateTime $value Дата
     * @return void
     */
    function setCreatedAtLt($value);

    /**
     * Возвращает дату автоматического закрытия от которой будут возвращены сделки или null, если дата не была установлена
     * @return \DateTime|null Время автоматического закрытия, от (включительно)
     */
    function getExpiresAtGte();

    /**
     * Проверяет, была ли установлена дата автоматического закрытия от которой выбираются сделки
     * @return bool True если дата была установлена, false если нет
     */
    function hasExpiresAtGte();

    /**
     * Устанавливает дату автоматического закрытия от которой выбираются сделки
     * @param \DateTime $value Дата
     * @return void
     */
    function setExpiresAtGte($value);

    /**
     * Возвращает дату автоматического закрытия от которой будут возвращены сделки или null, если дата не была установлена
     * @return \DateTime|null Время автоматического закрытия, от (не включая)
     */
    function getExpiresAtGt();

    /**
     * Проверяет, была ли установлена дата автоматического закрытия от которой выбираются сделки
     * @return bool True если дата была установлена, false если нет
     */
    function hasExpiresAtGt();

    /**
     * Устанавливает дату автоматического закрытия от которой выбираются сделки
     * @param \DateTime $value Дата автоматического закрытия
     * @return void
     */
    function setExpiresAtGt($value);

    /**
     * Возвращает дату автоматического закрытия до которой будут возвращены сделки или null, если дата не была установлена
     * @return \DateTime|null Время автоматического закрытия, до (включительно)
     */
    function getExpiresAtLte();

    /**
     * Проверяет, была ли установлена дата автоматического закрытия до которой выбираются сделки
     * @return bool True если дата была установлена, false если нет
     */
    function hasExpiresAtLte();

    /**
     * Устанавливает дату автоматического закрытия до которой выбираются сделки
     * @param \DateTime $value Дата автоматического закрытия
     * @return void
     */
    function setExpiresAtLte($value);

    /**
     * Возвращает дату автоматического закрытия до которой будут возвращены сделки или null, если дата не была установлена
     * @return \DateTime|null Время автоматического закрытия, до (не включая)
     */
    function getExpiresAtLt();

    /**
     * Проверяет, была ли установлена автоматического закрытия до которой выбираются сделки
     * @return bool True если дата была установлена, false если нет
     */
    function hasExpiresAtLt();

    /**
     * Устанавливает дату автоматического закрытия до которой выбираются сделки
     * @param \DateTime $value Дата автоматического закрытия
     * @return void
     */
    function setExpiresAtLt($value);

    /**
     * Возвращает ограничение количества объектов сделок или null, если оно до этого не было установлено
     * @return string|null Ограничение количества объектов сделок
     */
    function getLimit();

    /**
     * Проверяет, было ли установлено ограничение количества объектов сделок
     * @return bool True если ограничение количества объектов сделок было установлено, false если нет
     */
    function hasLimit();

    /**
     * Устанавливает ограничение количества объектов сделок
     * @param int $value Количества объектов сделок на странице
     * @return void
     */
    function setLimit($value);

    /**
     * Возвращает статус выбираемых сделок или null, если он до этого не был установлен
     * @return string|null Статус выбираемых сделок
     */
    function getStatus();

    /**
     * Проверяет, был ли установлен статус выбираемых сделок
     * @return bool True если статус был установлен, false если нет
     */
    function hasStatus();

    /**
     * Устанавливает статус выбираемых сделок
     * @param string $value Статус сделок
     * @return void
     */
    function setStatus($value);

    /**
     * Возвращает фильтр по описанию сделки или null, если он до этого не был установлен
     * @return string|null Фильтр по описанию сделки
     */
    function getFullTextSearch();

    /**
     * Проверяет, был ли установлен фильтр по описанию сделки
     * @return bool True если фильтр по описанию сделки был установлен, false если нет
     */
    function hasFullTextSearch();

    /**
     * Устанавливает фильтр по описанию сделки
     * @param string $value Фильтр по описанию сделки
     * @return void
     */
    function setFullTextSearch($value);
}
