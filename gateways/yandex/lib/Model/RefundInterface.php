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
 * Interface RefundInterface
 *
 * @package YandexCheckout\Model
 *
 * @property-read string $id Идентификатор возврата платежа
 * @property-read string $paymentId Идентификатор платежа
 * @property-read string $payment_id Идентификатор платежа
 * @property-read string $status Статус возврата
 * @property-read \DateTime $createdAt Время создания возврата
 * @property-read \DateTime $create_at Время создания возврата
 * @property-read AmountInterface $amount Сумма возврата
 * @property-read string $receiptRegistration Статус регистрации чека
 * @property-read string $receipt_registration Статус регистрации чека
 * @property-read string $comment Комментарий, основание для возврата средств покупателю
 */
interface RefundInterface
{
    /**
     * Возвращает идентификатор возврата платежа
     * @return string Идентификатор возврата
     */
    function getId();

    /**
     * Возвращает идентификатор платежа
     * @return string Идентификатор платежа
     */
    function getPaymentId();

    /**
     * Возвращает статус текущего возврата
     * @return string Статус возврата
     */
    function getStatus();

    /**
     * Возвращает дату создания возврата
     * @return \DateTime Время создания возврата
     */
    function getCreatedAt();

    /**
     * Возвращает сумму возврата
     * @return AmountInterface Сумма возврата
     */
    function getAmount();

    /**
     * Возвращает статус регистрации чека
     * @return string Статус регистрации чека
     */
    function getReceiptRegistration();

    /**
     * Возвращает комментарий к возврату
     * @return string Комментарий, основание для возврата средств покупателю
     */
    function getComment();
}
