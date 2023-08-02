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

namespace YooKassa\Request\Payments\Payment;

use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Model\AmountInterface;
use YooKassa\Model\Deal\PaymentDealInfo;
use YooKassa\Model\MonetaryAmount;
use YooKassa\Model\ReceiptInterface;
use YooKassa\Model\TransferInterface;

/**
 * Interface CreateCaptureRequestInterface
 *
 * @package YooKassa
 *
 * @property-read MonetaryAmount $amount Подтверждаемая сумма оплаты
 * @property-read ReceiptInterface $receipt Данные фискального чека 54-ФЗ
 */
interface CreateCaptureRequestInterface
{
    /**
     * Возвращает подтверждаемую сумму оплаты
     * @return AmountInterface Подтверждаемая сумма оплаты
     */
    function getAmount();

    /**
     * Проверяет, была ли установлена сумма оплаты
     * @return bool True если сумма оплаты была установлена, false если нет
     */
    function hasAmount();

    /**
     * Устанавливает сумму оплаты
     * @param AmountInterface $value Сумма оплаты
     */
    function setAmount(AmountInterface $value);

    /**
     * Возвращает чек, если он есть
     * @return ReceiptInterface|null Данные фискального чека 54-ФЗ или null, если чека нет
     * @since 1.0.2
     */
    function getReceipt();

    /**
     * Проверяет наличие чека в создаваемом платеже
     * @return bool True если чек есть, false если нет
     * @since 1.0.2
     */
    function hasReceipt();

    /**
     * Устанавливает чек
     * @param ReceiptInterface|null $value Инстанс чека или null для удаления информации о чеке
     * @throws InvalidPropertyValueTypeException Выбрасывается если передан не инстанс класса чека и не null
     */
    function setReceipt($value);

    /**
     * Проверяет наличие данных о распределении денег
     * @return bool
     */
    function hasTransfers();

    /**
     * Возвращает данные о распределении денег
     * @return TransferInterface[]
     */
    function getTransfers();

    /**
     * Устанавливает transfers (массив распределения денег между магазинами)
     * @param TransferInterface[]|array|null $value
     */
    function setTransfers($value);

    /**
     * Проверяет наличие данных о сделке
     * @return bool
     */
    function hasDeal();

    /**
     * Возвращает данные о сделке
     * @return PaymentDealInfo
     */
    function getDeal();

    /**
     * Устанавливает данные о сделке
     * @param PaymentDealInfo|array|null $value
     */
    function setDeal($value);
}
