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

namespace YooKassa\Model;

/**
 * Interface ReceiptItemInterface
 *
 * @package YooKassa\Model
 *
 * @property-read string $description Наименование товара
 * @property-read float $quantity Количество
 * @property-read float $amount Суммарная стоимость покупаемого товара в копейках/центах
 * @property-read AmountInterface $price Цена товара
 * @property-read int $vatCode Ставка НДС, число 1-6
 * @property-read int $vat_code Ставка НДС, число 1-6
 * @property-read string $paymentSubject Признак предмета расчета
 * @property-read string $payment_subject Признак предмета расчета
 * @property-read string $paymentMode Признак способа расчета
 * @property-read string $payment_mode Признак способа расчета
 * @property-read string $productCode Код товара
 * @property-read string $product_code Код товара
 * @property-read string $countryOfOriginCode Код страны происхождения товара
 * @property-read string $country_of_origin_code Код страны происхождения товара
 * @property-read string $customsDeclarationNumber Номер таможенной декларации (от 1 до 32 символов)
 * @property-read string $customs_declaration_number Номер таможенной декларации (от 1 до 32 символов)
 * @property-read float $excise Сумма акциза товара с учетом копеек
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
     * @return float Сумма стоимости покупаемого товара
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
     * Возвращает признак предмета расчета
     * @return string|null Признак предмета расчета
     */
    function getPaymentSubject();

    /**
     * Возвращает признак способа расчета
     * @return string|null Признак способа расчета
     */
    function getPaymentMode();

    /**
     * Возвращает код товара — уникальный номер, который присваивается экземпляру товара при маркировке
     * @return string|null Код товара
     */
    function getProductCode();

    /**
     * Возвращает код страны происхождения товара по общероссийскому классификатору стран мира
     * @return string|null Код страны происхождения товара
     */
    function getCountryOfOriginCode();

    /**
     * Возвращает номер таможенной декларации
     * @return string|null Номер таможенной декларации (от 1 до 32 символов)
     */
    function getCustomsDeclarationNumber();

    /**
     * Возвращает сумму акциза товара с учетом копеек
     * @return float|null Сумма акциза товара с учетом копеек
     */
    function getExcise();

    /**
     * Возвращает информацию о поставщике товара или услуги.
     *
     * @return SupplierInterface
     */
    function getSupplier();

    /**
     * @return string
     */
    function getAgentType();

    /**
     * Проверяет, является ли текущий элемент чека доставкой
     * @return bool True если доставка, false если обычный товар
     */
    function isShipping();

}