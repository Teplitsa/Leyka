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

namespace YooKassa\Request\Receipts;

use YooKassa\Model\SettlementInterface;

/**
 * Interface ReceiptInterface
 *
 * @package YooKassa\Model
 *
 * @property-read string $id Идентификатор чека в ЮKassa.
 * @property-read string $type Тип чека в онлайн-кассе: приход "payment" или возврат "refund".
 * @property-read string $receiptRegistration Статус доставки данных для чека в онлайн-кассу ("pending", "succeeded" или "canceled").
 * @property-read string $receipt_registration Статус доставки данных для чека в онлайн-кассу ("pending", "succeeded" или "canceled").
 * @property-read string $status Статус доставки данных для чека в онлайн-кассу ("pending", "succeeded" или "canceled").
 * @property-read int $taxSystemCode Код системы налогообложения. Число 1-6.
 * @property-read int $tax_system_code Код системы налогообложения. Число 1-6.
 * @property-read ReceiptResponseItemInterface[] $items Список товаров в заказе
 * @property-read SettlementInterface[] $settlements Список товаров в заказе
 */
interface ReceiptResponseInterface
{
    /**
     * Возвращает идентификатор чека в ЮKassa
     *
     * @return string Идентификатор чека в ЮKassa.
     */
    public function getId();

    /**
     * Возвращает тип чека в онлайн-кассе
     *
     * @return string Тип чека в онлайн-кассе: приход "payment" или возврат "refund".
     */
    public function getType();

    /**
     * Возвращает статус доставки данных для чека в онлайн-кассу
     *
     *  @return string Статус доставки данных для чека в онлайн-кассу ("pending", "succeeded" или "canceled").
     */
    public function getStatus();

    /**
     * Возвращает код системы налогообложения
     *
     *  @return int Код системы налогообложения. Число 1-6.
     */
    public function getTaxSystemCode();

    /**
     * Возвращает список товаров в заказ
     *
     *  @return ReceiptResponseItemInterface[]
     */
    public function getItems();

    /**
     * Возвращает список расчетов
     *
     *  @return SettlementInterface[]
     */
    public function getSettlements();

    /**
     * Возвращает идентификатор магазин
     *
     * @return string|null
     */
    public function getOnBehalfOf();

    /**
     * Проверяет есть ли в чеке хотя бы одна позиция
     * @return bool True если чек не пуст, false если в чеке нет ни одной позиции
     */
    function notEmpty();
}