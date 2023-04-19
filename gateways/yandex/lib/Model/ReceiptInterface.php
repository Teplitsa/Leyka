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

namespace YooKassa\Model;

/**
 * Interface ReceiptInterface
 *
 * @package YooKassa
 *
 * @property-read ReceiptCustomerInterface $customer Информация о плательщике
 * @property-read ReceiptItemInterface[] $items Список товаров в заказе
 * @property-read int $taxSystemCode Код системы налогообложения. Число 1-6.
 * @property-read int $tax_system_code Код системы налогообложения. Число 1-6.
 */
interface ReceiptInterface
{
    /**
     * Возвращает Id объекта чека
     *
     * @return string Id объекта чека
     */
    public function getObjectId();

    /**
     * Возвращает информацию о плательщике
     *
     * @return ReceiptCustomerInterface Информация о плательщике
     */
    public function getCustomer();

    /**
     * Возвращает список позиций в текущем чеке
     *
     * @return ReceiptItemInterface[] Список товаров в заказе
     */
    public function getItems();

    /**
     * Возвращает массив оплат, обеспечивающих выдачу товара.
     *
     * @return SettlementInterface[] Массив оплат, обеспечивающих выдачу товара.
     */
    public function getSettlements();

    /**
     * Возвращает код системы налогообложения
     *
     * @return int Код системы налогообложения. Число 1-6.
     */
    public function getTaxSystemCode();

    /**
     * Проверяет есть ли в чеке хотя бы одна позиция
     *
     * @return bool True если чек не пуст, false если в чеке нет ни одной позиции
     */
    public function notEmpty();

    /**
     * Подгоняет стоимость товаров в чеке к общей цене заказа
     *
     * @param AmountInterface $orderAmount Общая стоимость заказа
     * @param bool $withShipping Поменять ли заодно и цену доставки
     */
    public function normalize(AmountInterface $orderAmount, $withShipping = false);
}
