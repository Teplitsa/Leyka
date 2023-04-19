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

use YooKassa\Model\AmountInterface;
use YooKassa\Model\Deal\RefundDealData;
use YooKassa\Model\ReceiptInterface;
use YooKassa\Model\SourceInterface;

/**
 * Интерфейс объекта запроса на возврат
 *
 * @package YooKassa
 *
 * @property-read string $paymentId Айди платежа для которого создаётся возврат
 * @property-read AmountInterface $amount Сумма возврата
 * @property-read string $description Комментарий к операции возврата, основание для возврата средств покупателю.
 * @property-read ReceiptInterface|array|null $receipt Инстанс чека или null
 */
interface CreateRefundRequestInterface
{
    /**
     * Возвращает айди платежа для которого создаётся возврат средств
     * @return string Айди платежа для которого создаётся возврат
     */
    function getPaymentId();

    /**
     * Возвращает сумму возвращаемых средств
     * @return AmountInterface Сумма возврата
     */
    function getAmount();

    /**
     * Проверяет, был ли установлена идентификатор платежа
     * @return bool True если идентификатор платежа был установлен, false если нет
     */
    function hasPaymentId();

    /**
     * Устанавливает комментарий к возврату
     * @param string $value Комментарий к операции возврата, основание для возврата средств покупателю
     */
    function setDescription($value);

    /**
     * Возвращает комментарий к возврату или null, если комментарий не задан
     * @return string Комментарий к операции возврата, основание для возврата средств покупателю.
     */
    function getDescription();

    /**
     * Проверяет задан ли комментарий к создаваемому возврату
     * @return bool True если комментарий установлен, false если нет
     */
    function hasDescription();

    /**
     * Устанавливает чек
     * @param ReceiptInterface|null $value Инстанс чека или null для удаления информации о чеке
     */
    function setReceipt($value);

    /**
     * Возвращает инстанс чека или null, если чек не задан
     * @return ReceiptInterface|null Инстанс чека или null
     */
    function getReceipt();

    /**
     * Проверяет задан ли чек
     * @return bool True если чек есть, false если нет
     */
    function hasReceipt();

    /**
     * Устанавливает информацию о распределении денег — сколько и в какой магазин нужно перевести
     * @param SourceInterface[] $value Информация о распределении денег
     */
    function setSources($value);

    /**
     * Возвращает информацию о распределении денег — сколько и в какой магазин нужно перевести
     * @return SourceInterface[] Информация о распределении денег
     */
    function getSources();

    /**
     * Проверяет наличие информации о распределении денег
     * @return bool
     */
    function hasSources();

    /**
     * Устанавливает информацию о сделке
     * @param RefundDealData $value Информация о сделке
     */
    function setDeal($value);

    /**
     * Возвращает информацию о сделке
     * @return RefundDealData Информация о сделке
     */
    function getDeal();

    /**
     * Проверяет наличие информации о сделке
     * @return bool
     */
    function hasDeal();
}
