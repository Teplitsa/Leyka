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

use DateTime;
use YooKassa\Model\PaymentMethod\AbstractPaymentMethod;
use YooKassa\Model\Payout\PayoutCancellationDetails;
use YooKassa\Model\Deal\PayoutDealInfo;

/**
 * Interface DealInterface
 *
 * @package YooKassa
 *
 * @property-read string $id Идентификатор выплаты
 * @property-read AmountInterface $amount Сумма выплаты
 * @property-read string $status Текущее состояние выплаты
 * @property-read AbstractPaymentMethod $payoutDestination Способ проведения выплаты
 * @property-read AbstractPaymentMethod $payout_destination Способ проведения выплаты
 * @property-read string $description Описание транзакции
 * @property-read DateTime $createdAt Время создания заказа
 * @property-read DateTime $created_at Время создания заказа
 * @property-read PayoutDealInfo $deal Сделка, в рамках которой нужно провести выплату
 * @property-read CancellationDetailsInterface $cancellationDetails Комментарий к отмене выплаты
 * @property-read CancellationDetailsInterface $cancellation_details Комментарий к отмене выплаты
 * @property-read Metadata $metadata Метаданные платежа указанные мерчантом
 * @property-read bool $test Признак тестовой операции
 */
interface PayoutInterface
{
    /**
     * Возвращает Id сделки
     *
     * @return string Id сделки
     */
    public function getId();

    /**
     * Возвращает баланс сделки
     *
     * @return MonetaryAmount Баланс сделки
     */
    public function getAmount();

    /**
     * Возвращает статус сделки
     *
     * @return string Статус сделки
     */
    public function getStatus();

    /**
     * Возвращает платежное средство продавца, на которое ЮKassa переводит оплату
     *
     * @return string Платежное средство продавца, на которое ЮKassa переводит оплату
     */
    public function getPayoutDestination();

    /**
     * Возвращает описание транзакции (не более 128 символов)
     *
     * @return string Описание транзакции
     */
    public function getDescription();

    /**
     * Возвращает время создания сделки
     *
     * @return DateTime Время создания сделки
     */
    public function getCreatedAt();

    /**
     * Возвращает сделку, в рамках которой нужно провести выплату.
     *
     * @return PayoutDealInfo Сделка, в рамках которой нужно провести выплату
     */
    public function getDeal();

    /**
     * Возвращает комментарий к статусу canceled: кто отменил выплату и по какой причине.
     *
     * @return PayoutCancellationDetails Комментарий к статусу canceled: кто отменил выплату и по какой причине
     */
    public function getCancellationDetails();

    /**
     * Возвращает дополнительные данные сделки
     *
     * @return Metadata Дополнительные данные сделки
     */
    public function getMetadata();

    /**
     * Возвращает признак тестовой операции
     *
     * @return bool Признак тестовой операции
     */
    public function getTest();
}
