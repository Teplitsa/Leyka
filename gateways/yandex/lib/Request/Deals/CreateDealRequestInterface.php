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

use YooKassa\Model\Metadata;

/**
 * Interface DealInterface
 *
 * @package YooKassa
 *
 * @property string $type Тип сделки
 * @property string $fee_moment Момент перечисления вознаграждения
 * @property string $feeMoment Момент перечисления вознаграждения
 * @property string $description Описание сделки
 * @property Metadata $metadata Дополнительные данные сделки
 */
interface CreateDealRequestInterface
{
    /**
     * Возвращает тип сделки
     *
     * @return string Тип сделки
     */
    public function getType();

    /**
     * Проверяет наличие типа в создаваемой сделке
     * @return bool True если тип сделки установлен, false если нет
     */
    function hasType();

    /**
     * Устанавливает тип сделки
     * @param string $value Тип сделки
     */
    function setType($value);

    /**
     * Возвращает момент перечисления вам вознаграждения платформы
     *
     * @return string Момент перечисления вознаграждения
     */
    public function getFeeMoment();

    /**
     * Проверяет наличие момента перечисления вознаграждения в создаваемой сделке
     * @return bool True если момент перечисления вознаграждения установлен, false если нет
     */
    function hasFeeMoment();

    /**
     * Устанавливает момент перечисления вознаграждения платформы
     * @param string $value Момент перечисления вознаграждения
     */
    function setFeeMoment($value);

    /**
     * Возвращает описание сделки (не более 128 символов).
     *
     * @return string Описание сделки
     */
    public function getDescription();

    /**
     * Проверяет наличие описания в создаваемой сделке
     * @return bool True если описание сделки установлено, false если нет
     */
    function hasDescription();

    /**
     * Устанавливает описание сделки
     * @param string $value Описание сделки
     */
    function setDescription($value);

    /**
     * Возвращает дополнительные данные сделки
     *
     * @return Metadata Дополнительные данные сделки
     */
    public function getMetadata();

    /**
     * Проверяет, были ли установлены метаданные сделки
     * @return bool True если метаданные были установлены, false если нет
     */
    function hasMetadata();

    /**
     * Устанавливает метаданные, привязанные к сделке
     * @param Metadata|array|null $value Метаданные сделки, устанавливаемые мерчантом
     */
    function setMetadata($value);
}
