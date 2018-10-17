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

namespace YandexCheckout\Model;

/**
 * Interface ReceiptItemInterface
 *
 * @package YandexCheckout\Model
 *
 * @property-read string $description Наименование товара
 * @property-read int $quantity Количество
 * @property-read int $amount  Суммарная стоимость покупаемого товара в копейках/центах
 * @property-read AmountInterface $price Цена товара
 * @property-read int $vatCode Ставка НДС, число 1-6
 * @property-read int $vat_code Ставка НДС, число 1-6
 */
interface ReceiptItemInterface
{
    /**
     * Возвращает наименование товара
     * @return string Наименование товара
     */
    function getDescription();

    /**
     * Возвращает количество товара
     * @return float Количество купленного товара
     */
    function getQuantity();

    /**
     * Возвращает общую стоимость покупаемого товара в копейках/центах
     * @return int Сумма стоимости покупаемого товара
     */
    function getAmount();

    /**
     * Возвращает цену товара
     * @return AmountInterface Цена товара
     */
    function getPrice();

    /**
     * Возвращает ставку НДС
     * @return int|null Ставка НДС, число 1-6, или null если ставка не задана
     */
    function getVatCode();

    /**
     * Проверяет, является ли текущий элемент чека доставкой
     * @return bool True если доставка, false если обычный товар
     */
    function isShipping();
}